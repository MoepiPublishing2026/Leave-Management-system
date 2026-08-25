<?php

namespace App\Support;

class EmailGreeting
{
    public static function displayName(?object $user, string $fallback = ''): string
    {
        if (! $user) {
            return $fallback;
        }

        $name = trim((string) (($user->first_name ?? '').' '.($user->last_name ?? '')));
        if ($name === '') {
            $name = trim((string) ($user->name ?? ''));
        }

        return $name !== '' ? $name : $fallback;
    }

    public static function hello(?string $name): string
    {
        $name = trim((string) $name);

        return $name !== '' ? "Hello {$name}," : 'Hello,';
    }

    public static function helloUser(?object $user): string
    {
        return self::hello(self::displayName($user));
    }
}
