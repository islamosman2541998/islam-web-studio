<?php

namespace App\Support;

class FontRegistry
{
    public const ARABIC = [
        'IBM Plex Sans Arabic' => 'IBM Plex Sans Arabic',
        'Cairo' => 'Cairo',
        'Tajawal' => 'Tajawal',
    ];

    public const ENGLISH = [
        'Cormorant Garamond' => 'Cormorant Garamond',
        'DM Sans' => 'DM Sans',
        'Inter' => 'Inter',
        'Poppins' => 'Poppins',
    ];

    public static function arabicOptions(): array
    {
        return self::ARABIC;
    }

    public static function englishOptions(): array
    {
        return self::ENGLISH;
    }

    public static function arabic(?string $font): string
    {
        return array_key_exists((string) $font, self::ARABIC) ? (string) $font : 'IBM Plex Sans Arabic';
    }

    public static function english(?string $font): string
    {
        return array_key_exists((string) $font, self::ENGLISH) ? (string) $font : 'Cormorant Garamond';
    }
}
