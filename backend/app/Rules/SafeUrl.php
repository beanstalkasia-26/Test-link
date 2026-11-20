<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || strlen($value) > 2048 || preg_match('/\s/', $value)) {
            $fail('The :attribute must be a valid link.');

            return;
        }

        if (self::isAllowed($value)) {
            return;
        }

        $fail('The :attribute must be a site path (e.g. /admissions) or a full https:// address.');
    }

    public static function isAllowed(string $value): bool
    {
        if (str_starts_with($value, '/') && ! str_starts_with($value, '//')) {
            return true;
        }

        if (str_starts_with($value, '#')) {
            return true;
        }

        if (preg_match('/^mailto:[^@\s]+@[^@\s]+$/i', $value) || preg_match('/^tel:\+?[0-9 ()-]{5,}$/i', $value)) {
            return true;
        }

        return in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true)
            && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}
