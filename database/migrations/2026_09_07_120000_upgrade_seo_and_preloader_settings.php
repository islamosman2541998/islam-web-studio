<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $this->upgradeTranslatedDefault('seo.title', [
            'ar' => 'إسلام ويب ستوديو | تطوير وتصميم وتسويق',
            'en' => 'Islam Web Studio | Development, design & marketing',
        ], [
            'ar' => 'إسلام ويب ستوديو | تطوير مواقع ومتاجر وأنظمة',
            'en' => 'Islam Web Studio | Websites, commerce & Laravel systems',
        ]);

        $this->upgradeTranslatedDefault('seo.description', [
            'ar' => 'إسلام ويب ستوديو: تطوير وتصميم وتسويق في رحلة واحدة واضحة، من الفكرة حتى الإطلاق.',
            'en' => 'Islam Web Studio brings development, design and marketing into one clear journey from idea to launch.',
        ], [
            'ar' => 'إسلام ويب ستوديو لتطوير المواقع والمتاجر الإلكترونية وأنظمة Laravel، مع خدمات التصميم والمحتوى والتسويق الرقمي لنمو مشروعك.',
            'en' => 'Islam Web Studio builds websites, e-commerce stores and Laravel systems, with design, content and digital marketing services for growing businesses.',
        ]);

        $this->insertIfMissing('seo', 'seo.keywords', [
            'ar' => 'تطوير مواقع، تصميم مواقع، متاجر إلكترونية، أنظمة Laravel، تسويق رقمي، إسلام ويب ستوديو',
            'en' => 'web development, web design, e-commerce, Laravel systems, digital marketing, Islam Web Studio',
        ]);

        $this->seedSearchLogo();

        foreach ($this->preloaderDefaults() as $key => $value) {
            $this->insertIfMissing('preloader', 'preloader.'.$key, $value);
        }
    }

    public function down(): void
    {
        // Content settings are deliberately preserved when rolling back schema migrations.
    }

    private function upgradeTranslatedDefault(string $key, array $old, array $new): void
    {
        $row = DB::table('settings')->where('key', $key)->first();

        if (! $row) {
            $this->insertIfMissing('seo', $key, $new);

            return;
        }

        if ($this->decode($row->value) === $old) {
            DB::table('settings')->where('key', $key)->update([
                'value' => $this->encode($new),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedSearchLogo(): void
    {
        $current = DB::table('settings')->where('key', 'seo.logo')->first();

        if ($current && filled($this->decode($current->value))) {
            return;
        }

        $logo = $this->settingValue('general.logo_light') ?: $this->settingValue('general.favicon');

        if (! $logo) {
            return;
        }

        DB::table('settings')->updateOrInsert(
            ['key' => 'seo.logo'],
            [
                'group' => 'seo',
                'value' => $this->encode($logo),
                'created_at' => $current?->created_at ?? now(),
                'updated_at' => now(),
            ],
        );
    }

    private function settingValue(string $key): mixed
    {
        $value = DB::table('settings')->where('key', $key)->value('value');

        return $this->decode($value);
    }

    private function insertIfMissing(string $group, string $key, mixed $value): void
    {
        if (DB::table('settings')->where('key', $key)->exists()) {
            return;
        }

        DB::table('settings')->insert([
            'group' => $group,
            'key' => $key,
            'value' => $this->encode($value),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function decode(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return is_string($value) ? json_decode($value, true) : $value;
    }

    private function encode(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function preloaderDefaults(): array
    {
        return [
            'show_logo' => true,
            'logo' => null,
            'logo_width' => 84,
            'show_text' => false,
            'text' => ['ar' => 'بنجهّز لك التجربة', 'en' => 'Preparing your experience'],
            'text_color' => '#F7F4D5',
            'text_size' => 13,
            'background_type' => 'color',
            'background_color' => '#0A3323',
            'background_opacity' => 100,
            'background_image' => null,
            'background_image_opacity' => 100,
            'background_fit' => 'cover',
            'overlay_color' => '#0A3323',
            'overlay_opacity' => 30,
            'panel_enabled' => false,
            'panel_color' => '#071F17',
            'panel_opacity' => 45,
            'panel_radius' => 24,
            'show_progress' => true,
            'progress_color' => '#D3968C',
            'minimum_duration' => 850,
        ];
    }
};
