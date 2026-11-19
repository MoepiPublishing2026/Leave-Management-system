<?php

namespace App\Support;

class PersonName
{
    /** Must start with a capital letter; letters only after that. */
    public const REGEX = '/^[A-Z][a-zA-Z]*$/';

    /** @return list<string|\Illuminate\Validation\Rules\Regex> */
    public static function rules(int $max = 15): array
    {
        return ['required', 'string', 'max:'.$max, 'regex:'.self::REGEX];
    }

    /** @return array<string, string> */
    public static function messages(string $fieldLabel = 'Name'): array
    {
        $label = trim($fieldLabel) !== '' ? $fieldLabel : 'Name';

        return [
            'regex' => $label.' must start with a capital letter and contain letters only.',
        ];
    }

    public static function normalize(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        // Keep the rest of the letters as typed, but force the first character uppercase.
        return mb_strtoupper(mb_substr($value, 0, 1)).mb_substr($value, 1);
    }

    public static function full(?string $first, ?string $last): string
    {
        return trim(self::normalize($first).' '.self::normalize($last));
    }
}
