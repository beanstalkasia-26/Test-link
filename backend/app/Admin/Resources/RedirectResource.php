<?php

namespace App\Admin\Resources;

use App\Enums\Permission;
use App\Models\Redirect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RedirectResource extends Resource
{
    public function model(): string
    {
        return Redirect::class;
    }

    public function slug(): string
    {
        return 'redirects';
    }

    public function label(): string
    {
        return 'Redirects';
    }

    public function singular(): string
    {
        return 'Redirect';
    }

    public function titleAttribute(): string
    {
        return 'from_path';
    }

    public function navigationGroup(): string
    {
        return 'Website';
    }

    public function permission(): string
    {
        return Permission::RedirectsManage;
    }

    public function fields(): array
    {
        return [
            ['name' => 'from_path', 'type' => 'text', 'label' => 'From path', 'required' => true, 'max' => 700, 'help' => 'Old address on this site, e.g. /old-page'],
            ['name' => 'to_url', 'type' => 'url', 'label' => 'To', 'required' => true],
            ['name' => 'status_code', 'type' => 'select', 'label' => 'Type', 'required' => true, 'options' => ['301' => 'Permanent (301)', '302' => 'Temporary (302)']],
        ];
    }

    public function rules(Request $request, ?Model $record): array
    {
        $rules = parent::rules($request, $record);
        $rules['from_path'][] = 'regex:/^\/[^\s]*$/';
        $rules['from_path'][] = Rule::unique('redirects', 'from_path')->ignore($record?->getKey());

        return $rules;
    }

    public function extract(array $validated, ?Model $record): array
    {
        [$attributes, $relations, $media] = parent::extract($validated, $record);
        $attributes['from_path'] = Redirect::normalizePath($attributes['from_path']);
        $attributes['status_code'] = (int) $attributes['status_code'];

        return [$attributes, $relations, $media];
    }

    public function messages(): array
    {
        return ['from_path.regex' => 'The path must start with "/" and contain no spaces.', 'from_path.unique' => 'A redirect from this path already exists.'];
    }

    public function columns(): array
    {
        return [['key' => 'title', 'label' => 'From'], ['key' => 'to', 'label' => 'To'], ['key' => 'hits', 'label' => 'Hits']];
    }

    public function row(Model $record): array
    {
        return parent::row($record) + ['to' => $record->to_url, 'hits' => $record->hits];
    }
}
