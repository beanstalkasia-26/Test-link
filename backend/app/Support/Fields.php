<?php

namespace App\Support;

use App\Rules\SafeUrl;
use Illuminate\Validation\Rule;

class Fields
{
    public static function rules(array $fields, string $prefix = ''): array
    {
        $rules = [];
        foreach ($fields as $field) {
            $key = ltrim($prefix.'.'.$field['name'], '.');
            $required = ($field['required'] ?? false) ? 'required' : 'nullable';

            $rule = match ($field['type']) {
                'text', 'slug' => [$required, 'string', 'max:'.($field['max'] ?? 255)],
                'email' => [$required, 'string', 'email:rfc', 'max:255'],
                'textarea' => [$required, 'string', 'max:'.($field['max'] ?? 2000)],
                'richtext' => [$required, 'string', 'max:200000'],
                'url' => [$required, 'string', new SafeUrl],
                'image' => [$required, 'integer', Rule::exists('media', 'id')->where('kind', 'image')],
                'file' => [$required, 'integer', Rule::exists('media', 'id')->where('kind', 'document')],
                'select' => [$required, Rule::in(array_map('strval', array_keys($field['options'] ?? [])))],
                'reference' => [$required, 'string', Rule::in(array_map('strval', array_keys(OptionSources::get($field['source']))))],
                'belongsTo' => [$required, 'integer', Rule::in(array_keys(OptionSources::get($field['source'])))],
                'belongsToMany', 'blocks' => ['nullable', 'array'],
                'boolean' => ['nullable', 'boolean'],
                'number' => [$required, 'integer', 'min:'.($field['min'] ?? 0), 'max:'.($field['max'] ?? 100000)],
                'date' => [$required, 'date'],
                'datetime' => [$required, 'date'],
                'repeater' => [($field['minItems'] ?? 0) > 0 ? 'required' : 'nullable', 'array', 'min:'.($field['minItems'] ?? 0), 'max:'.($field['maxItems'] ?? 50)],
                'group' => ['nullable', 'array'],
                default => null,
            };

            if ($rule === null) {
                continue;
            }

            $rules[$key] = $rule;

            if ($field['type'] === 'belongsToMany') {
                $rules[$key.'.*'] = ['integer', Rule::in(array_keys(OptionSources::get($field['source'])))];
            }

            if ($field['type'] === 'blocks') {
                $rules[$key.'.*'] = ['string', Rule::in(array_map('strval', array_keys($field['options'] ?? [])))];
            }

            if ($field['type'] === 'repeater') {
                $rules[$key.'.*'] = ['array'];
                $rules[$key.'.*._hidden'] = ['nullable', 'boolean'];
                $rules += self::rules($field['fields'], $key.'.*');
            }

            if ($field['type'] === 'group') {
                $rules += self::rules($field['fields'], $key);
            }
        }

        return $rules;
    }

    public static function normalize(array $fields, array $data): array
    {
        $clean = [];
        foreach ($fields as $field) {
            $value = $data[$field['name']] ?? null;
            $clean[$field['name']] = match ($field['type']) {
                'text', 'textarea', 'url', 'select', 'reference', 'slug', 'email', 'date', 'datetime' => is_scalar($value) && trim((string) $value) !== '' ? trim((string) $value) : null,
                'richtext' => HtmlSanitizer::clean(is_string($value) ? $value : null),
                'image', 'file', 'number', 'belongsTo' => is_numeric($value) ? (int) $value : null,
                'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'belongsToMany' => array_values(array_unique(array_map('intval', is_array($value) ? $value : []))),
                'blocks' => array_values(array_intersect(array_map('strval', array_keys($field['options'] ?? [])), is_array($value) ? $value : [])),
                'repeater' => array_values(array_map(
                    fn ($item) => self::normalizeItem($field['fields'], is_array($item) ? $item : []),
                    self::inSubmittedOrder($value),
                )),
                'group' => self::normalize($field['fields'], is_array($value) ? $value : []),
                default => $value,
            };
        }

        return $clean;
    }

    private static function normalizeItem(array $fields, array $item): array
    {
        $clean = self::normalize($fields, $item);

        return filter_var($item['_hidden'] ?? false, FILTER_VALIDATE_BOOLEAN) ? $clean + ['_hidden' => true] : $clean;
    }

    public static function withoutHiddenItems(array $fields, array $data): array
    {
        foreach ($fields as $field) {
            $name = $field['name'];
            if ($field['type'] === 'repeater' && is_array($data[$name] ?? null)) {
                $data[$name] = array_values(array_map(
                    fn ($item) => self::withoutHiddenItems($field['fields'], $item),
                    array_filter($data[$name], fn ($item) => is_array($item) && empty($item['_hidden'])),
                ));
            }
            if ($field['type'] === 'group' && is_array($data[$name] ?? null)) {
                $data[$name] = self::withoutHiddenItems($field['fields'], $data[$name]);
            }
        }

        return $data;
    }

    private static function inSubmittedOrder(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }
        ksort($value, SORT_NUMERIC);

        return $value;
    }

    public static function mediaIds(array $fields, array $data): array
    {
        $ids = [];
        foreach ($fields as $field) {
            $value = $data[$field['name']] ?? null;
            if (in_array($field['type'], ['image', 'file'], true) && is_numeric($value)) {
                $ids[] = (int) $value;
            }
            if ($field['type'] === 'repeater' && is_array($value)) {
                foreach ($value as $item) {
                    $ids = array_merge($ids, self::mediaIds($field['fields'], is_array($item) ? $item : []));
                }
            }
            if ($field['type'] === 'group' && is_array($value)) {
                $ids = array_merge($ids, self::mediaIds($field['fields'], $value));
            }
        }

        return array_values(array_unique($ids));
    }

    public static function forAdmin(array $fields): array
    {
        return array_map(function (array $field): array {
            if (isset($field['source'])) {
                $field['options'] = collect(OptionSources::get($field['source']))
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all();
            } elseif (isset($field['options']) && ! array_is_list($field['options'])) {
                $field['options'] = collect($field['options'])->map(fn ($label, $value) => ['value' => (string) $value, 'label' => $label])->values()->all();
            }
            if (isset($field['fields'])) {
                $field['fields'] = self::forAdmin($field['fields']);
            }

            return $field;
        }, $fields);
    }
}
