<?php

namespace App\Admin\Resources;

use App\Enums\ContentStatus;
use App\Enums\Permission;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSeo;
use App\Models\ContentRevision;
use App\Sections\SectionRegistry;
use App\Support\Fields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

abstract class Resource
{
    abstract public function model(): string;

    abstract public function slug(): string;

    abstract public function label(): string;

    abstract public function singular(): string;

    abstract public function fields(): array;

    public function navigationGroup(): string
    {
        return 'Content';
    }

    public function permission(): string
    {
        return Permission::ContentEdit;
    }

    public function titleAttribute(): string
    {
        return 'title';
    }

    public function searchable(): array
    {
        return [$this->titleAttribute()];
    }

    public function with(): array
    {
        return [];
    }

    public function defaultSort(): array
    {
        return in_array('sort_order', (new ($this->model()))->getFillable(), true) ? ['sort_order', 'asc'] : ['updated_at', 'desc'];
    }

    public function isPublishable(): bool
    {
        return in_array(HasPublication::class, class_uses_recursive($this->model()), true);
    }

    public function hasSeo(): bool
    {
        return in_array(HasSeo::class, class_uses_recursive($this->model()), true);
    }

    public function newModel(): Model
    {
        return new ($this->model());
    }

    public function query(): Builder
    {
        return $this->model()::query()->with($this->with());
    }

    public function allFields(): array
    {
        $fields = $this->fields();

        if ($this->hasSeo()) {
            $fields = array_merge($fields, [
                ['name' => 'meta_title', 'type' => 'text', 'label' => 'SEO title', 'max' => 70, 'group' => 'SEO', 'help' => 'Shown in search results and browser tabs. Defaults to the title. Aim for 50–60 characters.'],
                ['name' => 'meta_description', 'type' => 'textarea', 'label' => 'Meta description', 'max' => 170, 'group' => 'SEO', 'help' => 'Summary for search results, 120–160 characters.'],
                ['name' => 'og_image_id', 'type' => 'image', 'label' => 'Social sharing image', 'group' => 'SEO', 'help' => 'Used when the page is shared on social media (1200×630 recommended).'],
                ['name' => 'canonical_url', 'type' => 'url', 'label' => 'Canonical URL override', 'group' => 'SEO', 'help' => 'Leave blank unless this content is duplicated elsewhere.'],
                ['name' => 'noindex', 'type' => 'boolean', 'label' => 'Hide from search engines (noindex)', 'group' => 'SEO'],
            ]);
        }

        return $fields;
    }

    private function draftSectionRules(array $rules): array
    {
        return array_map(fn (array $fieldRules): array => array_values(array_filter(
            array_map(fn ($rule) => $rule === 'required' ? 'nullable' : $rule, $fieldRules),
            fn ($rule) => ! (is_string($rule) && str_starts_with($rule, 'min:') && in_array('array', $fieldRules, true)),
        )), $rules);
    }

    public function rules(Request $request, ?Model $record): array
    {
        $rules = Fields::rules($this->allFields());

        if ($this->hasSectionsField()) {
            $rules['sections'] = ['nullable', 'array', 'max:40'];
            $rules['sections.*.type'] = ['required', Rule::in(array_keys(SectionRegistry::definitions()))];
            $rules['sections.*.label'] = ['nullable', 'string', 'max:120'];
            $rules['sections.*.anchor'] = ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'];
            $rules['sections.*.is_visible'] = ['nullable', 'boolean'];
            foreach ((array) $request->input('sections', []) as $index => $section) {
                $type = is_array($section) ? ($section['type'] ?? null) : null;
                if (is_string($type) && SectionRegistry::exists($type)) {
                    $sectionRules = SectionRegistry::rules($type, "sections.$index.data");
                    $rules += filter_var($section['is_visible'] ?? true, FILTER_VALIDATE_BOOLEAN) ? $sectionRules : $this->draftSectionRules($sectionRules);
                }
            }
        }

        foreach ($this->allFields() as $field) {
            if ($field['type'] === 'slug') {
                $rules[$field['name']] = array_merge($rules[$field['name']], [
                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    $this->slugUniqueRule($request, $record),
                ]);
            }
        }

        return $rules;
    }

    protected function slugUniqueRule(Request $request, ?Model $record): mixed
    {
        $model = $this->newModel();

        return Rule::unique($model->getTable(), 'slug')->ignore($record?->getKey());
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'The URL slug may only contain lowercase letters, numbers and single hyphens.',
            'slug.unique' => 'Another item already uses this URL slug.',
        ];
    }

    public function extract(array $validated, ?Model $record): array
    {
        $fields = $this->allFields();
        $data = Fields::normalize($fields, $validated);
        $attributes = [];
        $relations = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            if (isset($field['store'])) {
                $attributes[$field['store']][$name] = $data[$name] ?? null;

                continue;
            }
            match ($field['type']) {
                'sections' => null,
                'belongsToMany' => $relations[$field['relation'] ?? $name] = $data[$name],
                'number' => $attributes[$name] = $data[$name] ?? 0,
                default => $attributes[$name] = $data[$name] ?? null,
            };
        }

        foreach ($fields as $field) {
            if ($field['type'] === 'slug' && empty($attributes[$field['name']])) {
                $attributes[$field['name']] = Str::slug((string) ($attributes[$field['from'] ?? $this->titleAttribute()] ?? ''));
            }
        }

        $media = Fields::mediaIds($fields, $data);

        if ($this->hasSectionsField()) {
            unset($attributes['sections']);
            $submitted = $validated['sections'] ?? [];
            ksort($submitted, SORT_NUMERIC);
            $sections = [];
            foreach (array_values($submitted) as $section) {
                $sectionData = SectionRegistry::normalize($section['type'], $section['data'] ?? []);
                $media = array_merge($media, SectionRegistry::mediaIds($section['type'], $sectionData));
                $sections[] = [
                    'type' => $section['type'],
                    'label' => $section['label'] ?? null,
                    'anchor' => isset($section['anchor']) ? Str::slug($section['anchor']) : null,
                    'is_visible' => filter_var($section['is_visible'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'data' => $sectionData,
                ];
            }
            $relations['sections'] = $sections;
        }

        return [$attributes, $relations, array_values(array_unique($media))];
    }

    public function hasSectionsField(): bool
    {
        return collect($this->fields())->contains('type', 'sections');
    }

    public function formValues(Model $record): array
    {
        if ($this->isPublishable()) {
            $working = $record->workingCopy()->first();
            $snapshot = $working?->data ?? $record->toSnapshot();
            $values = array_merge($snapshot['attributes'] ?? [], $snapshot['relations'] ?? []);
        } else {
            $values = $record->only([...array_column($this->allFields(), 'name'), ...array_filter(array_column($this->allFields(), 'store'))]);
        }

        foreach ($this->allFields() as $field) {
            $value = isset($field['store']) ? ($values[$field['store']][$field['name']] ?? null) : ($values[$field['name']] ?? null);
            if ($value instanceof \DateTimeInterface) {
                $value = $field['type'] === 'date' ? $value->format('Y-m-d') : $value->format('Y-m-d\TH:i');
            } elseif (is_string($value) && $field['type'] === 'date') {
                $value = substr($value, 0, 10);
            } elseif (is_string($value) && $field['type'] === 'datetime') {
                $value = str_replace(' ', 'T', substr($value, 0, 16));
            }
            $values[$field['name']] = $value;
        }

        return $values;
    }

    public function defaults(): array
    {
        return [];
    }

    public function columns(): array
    {
        return [['key' => 'title', 'label' => 'Title']];
    }

    public function row(Model $record): array
    {
        $row = [
            'id' => $record->getKey(),
            'title' => $record->getAttribute($this->titleAttribute()),
            'updated_at' => $record->updated_at?->diffForHumans(),
        ];

        if ($this->isPublishable()) {
            $row += [
                'status' => $record->status->value,
                'status_label' => $record->status->label(),
                'review_state' => $record->review_state,
                'scheduled_for' => $record->scheduled_for?->toDayDateTimeString(),
                'has_pending_changes' => $record->relationLoaded('workingCopy') ? $record->workingCopy !== null : false,
                'url' => $record->isLive() ? $record->publicPath() : null,
            ];
        }

        return $row;
    }

    public function meta(): array
    {
        return [
            'slug' => $this->slug(),
            'label' => $this->label(),
            'singular' => $this->singular(),
            'publishable' => $this->isPublishable(),
            'columns' => $this->columns(),
            'statuses' => $this->isPublishable() ? ContentStatus::options() : [],
        ];
    }

    public function revisions(Model $record): array
    {
        if (! $this->isPublishable()) {
            return [];
        }

        return $record->revisions()->with('user:id,name')->limit(30)->get()->map(fn (ContentRevision $revision): array => [
            'id' => $revision->id,
            'kind' => $revision->kind,
            'note' => $revision->note,
            'user' => $revision->user?->name,
            'created_at' => $revision->created_at?->toDayDateTimeString(),
        ])->all();
    }
}
