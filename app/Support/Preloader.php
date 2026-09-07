<?php

namespace App\Support;

use App\Models\Asset;

class Preloader
{
    /** @param array<string, mixed>|null $values */
    public static function make(?array $values = null): array
    {
        $definitions = SettingsRegistry::groups()['preloader'];
        $read = static function (string $key) use ($definitions, $values): mixed {
            $default = $definitions[$key][3] ?? null;

            return $values !== null
                ? ($values[$key] ?? $default)
                : Studio::setting('preloader.'.$key, $default);
        };
        $asset = static fn (mixed $id): ?Asset => Asset::query()
            ->where('kind', 'image')
            ->where('visibility', 'public')
            ->where('is_active', true)
            ->find(is_numeric($id) ? (int) $id : null);
        $clamp = static fn (mixed $value, int $min, int $max, int $fallback): int => is_numeric($value)
            ? max($min, min($max, (int) $value))
            : $fallback;

        $logo = $asset($read('logo'));
        $background = $asset($read('background_image'));
        $backgroundType = in_array($read('background_type'), ['color', 'image'], true) ? $read('background_type') : 'color';
        $fit = in_array($read('background_fit'), ['cover', 'contain'], true) ? $read('background_fit') : 'cover';
        $animation = in_array($read('animation'), ['fade', 'pulse', 'scale', 'float', 'spin', 'draw'], true) ? $read('animation') : 'pulse';
        $text = $read('text');
        $text = is_array($text) ? (string) ($text[app()->getLocale()] ?? '') : (string) $text;

        return [
            'enabled' => (bool) $read('enabled'),
            'transitions' => (bool) $read('transitions'),
            'show_logo' => (bool) $read('show_logo'),
            'logo_url' => $logo?->imageUrl(480) ?: asset('brand/mark-light.svg'),
            'logo_width' => $clamp($read('logo_width'), 40, 240, 84),
            'show_text' => (bool) $read('show_text'),
            'text' => $text,
            'text_color' => Studio::color($read('text_color'), '#F7F4D5'),
            'text_size' => $clamp($read('text_size'), 9, 48, 13),
            'background_type' => $backgroundType,
            'background_color' => Studio::rgba($read('background_color'), $read('background_opacity'), '#0A3323'),
            'background_image_url' => $background?->imageUrl(1920),
            'background_image_opacity' => $clamp($read('background_image_opacity'), 0, 100, 100) / 100,
            'background_fit' => $fit,
            'overlay_color' => Studio::rgba($read('overlay_color'), $read('overlay_opacity'), '#0A3323'),
            'panel_enabled' => (bool) $read('panel_enabled'),
            'panel_color' => Studio::rgba($read('panel_color'), $read('panel_opacity'), '#071F17'),
            'panel_radius' => $clamp($read('panel_radius'), 0, 48, 24),
            'show_progress' => (bool) $read('show_progress'),
            'progress_color' => Studio::color($read('progress_color'), '#D3968C'),
            'animation' => $animation,
            'minimum_duration' => $clamp($read('minimum_duration'), 0, 5000, 850),
            'duration' => $clamp($read('duration'), 100, 1200, 400),
        ];
    }
}
