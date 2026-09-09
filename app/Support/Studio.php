<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class Studio
{
    public static function settings(): array
    {
        return Cache::memo()->remember('studio.settings', 3600, fn () => Schema::hasTable('settings') ? Setting::query()->pluck('value', 'key')->all() : []);
    }

    public static function setting(string $key, mixed $default = null): mixed
    {
        return self::settings()[$key] ?? $default;
    }

    public static function translated(string $key, string $default = ''): string
    {
        $value = self::setting($key, []);
        if (is_array($value)) {
            $locale = app()->getLocale();

            return array_key_exists($locale, $value) ? (string) ($value[$locale] ?? '') : $default;
        }

        return (string) $value;
    }

    public static function put(string $key, mixed $value, string $group = 'general'): void
    {
        Setting::updateOrCreate(['key' => $key], ['group' => $group, 'value' => $value]);
    }

    public static function flush(): void
    {
        foreach (['studio.settings', 'studio.translations', 'studio.sitemap'] as $key) {
            Cache::memo()->forget($key);
        }
        Cache::memo()->forever('studio.menu.version', (string) Str::uuid());
    }

    public static function translations(): array
    {
        return Cache::memo()->remember('studio.translations', 3600, fn () => Schema::hasTable('translations') ? Translation::where('is_active', true)->get()->mapWithKeys(fn (Translation $row) => [$row->key => $row->storedValues()])->all() : []);
    }

    public static function text(string $key, array $replace = []): string
    {
        $text = self::translations()[$key][app()->getLocale()] ?? __('studio.'.$key);
        foreach ($replace as $name => $value) {
            $text = str_replace(':'.$name, (string) $value, $text);
        }

        return $text;
    }

    public static function safeUrl(?string $url): string
    {
        if (! $url) {
            return '#';
        }
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return url($url);
        }

        return preg_match('~^(https?://|mailto:|tel:)~i', $url) ? $url : '#';
    }

    public static function whatsappNumber(): ?string
    {
        $rawNumber = self::setting('general.whatsapp') ?: self::setting('general.phone');
        $number = preg_replace('/\D+/', '', (string) $rawNumber);

        if (str_starts_with($number, '00')) {
            $number = substr($number, 2);
        }

        if (str_starts_with($number, '0')) {
            $number = '20'.substr($number, 1);
        }

        return preg_match('/^[1-9][0-9]{7,14}$/', $number) ? $number : null;
    }

    public static function color(?string $color, string $fallback): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $color) ? $color : $fallback;
    }

    public static function rgba(?string $color, mixed $opacity, string $fallback): string
    {
        [$red, $green, $blue] = sscanf(self::color($color, $fallback), '#%02x%02x%02x');
        $opacity = is_numeric($opacity) ? (float) $opacity : 100;
        $alpha = max(0, min(100, $opacity)) / 100;
        $alpha = rtrim(rtrim(number_format($alpha, 2, '.', ''), '0'), '.');

        return sprintf('rgba(%d,%d,%d,%s)', $red, $green, $blue, $alpha);
    }

    public static function palette(string $color): array
    {
        $rgb = sscanf(self::color($color, '#105666'), '#%02x%02x%02x');
        $palette = [];
        foreach ([50 => 0.94, 100 => 0.85, 200 => 0.7, 300 => 0.5, 400 => 0.25, 500 => 0, 600 => -0.08, 700 => -0.2, 800 => -0.35, 900 => -0.5, 950 => -0.65] as $shade => $mix) {
            $values = array_map(fn ($value) => (int) round($mix >= 0 ? $value + (255 - $value) * $mix : $value * (1 + $mix)), $rgb);
            $palette[$shade] = sprintf('#%02x%02x%02x', ...$values);
        }

        return $palette;
    }

    public static function cleanHtml(?string $html): string
    {
        $config = (new HtmlSanitizerConfig)->allowSafeElements()->allowRelativeLinks()->allowLinkSchemes(['https', 'http', 'mailto']);

        return (new HtmlSanitizer($config))->sanitize($html ?? '');
    }
}
