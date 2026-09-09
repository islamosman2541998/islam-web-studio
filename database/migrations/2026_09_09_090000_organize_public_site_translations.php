<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> */
    private const FROM_TRANSLATION = [
        'services_page_title' => 'services_title',
        'services_page_intro' => 'services_intro',
        'projects_page_title' => 'projects_title',
        'projects_page_intro' => 'projects_intro',
        'posts_page_title' => 'posts_title',
        'posts_page_intro' => 'posts_intro',
        'methodology_page_title' => 'methodology_title',
        'methodology_page_intro' => 'methodology_intro',
        'testimonials_page_title' => 'testimonials_title',
        'testimonials_page_intro' => 'testimonials_intro',
        'service_related_title' => 'selected_work',
        'post_related_title' => 'insights',
    ];

    /** @var array<string, string> */
    private const FROM_SETTING = [
        'home_intro_media_caption' => 'home.intro_media_caption',
        'home_intro_label' => 'home.intro_label',
        'home_intro_title' => 'home.intro_title',
        'home_intro_text' => 'home.intro_text',
        'home_intro_link_text' => 'home.intro_link_text',
        'home_intro_founder_label' => 'home.intro_founder_label',
        'home_intro_signature' => 'home.intro_signature',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('translations')) {
            return;
        }

        foreach (self::FROM_TRANSLATION as $key => $sourceKey) {
            $this->ensureTranslation($key, $this->translationValue($sourceKey));
        }

        foreach (self::FROM_SETTING as $key => $settingKey) {
            $this->ensureTranslation($key, $this->settingValue($settingKey));
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->whereIn('key', array_values(self::FROM_SETTING))->delete();
        }
    }

    public function down(): void
    {
        // Keep translated copy so rolling back never removes edited website text.
    }

    /** @param array{ar: string, en: string}|null $value */
    private function ensureTranslation(string $key, ?array $value): void
    {
        $existing = DB::table('translations')->where('key', $key)->first();

        if ($existing) {
            DB::table('translations')->where('key', $key)->update([
                'is_active' => true,
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

            return;
        }

        $value ??= [
            'ar' => (string) trans('studio.'.$key, [], 'ar'),
            'en' => (string) trans('studio.'.$key, [], 'en'),
        ];

        DB::table('translations')->insert([
            'key' => $key,
            'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @return array{ar: string, en: string}|null */
    private function translationValue(string $key): ?array
    {
        $value = DB::table('translations')->where('key', $key)->value('value');

        return $this->translatedArray($value);
    }

    /** @return array{ar: string, en: string}|null */
    private function settingValue(string $key): ?array
    {
        if (! Schema::hasTable('settings')) {
            return null;
        }

        return $this->translatedArray(DB::table('settings')->where('key', $key)->value('value'));
    }

    /** @return array{ar: string, en: string}|null */
    private function translatedArray(mixed $value): ?array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($decoded) || (! array_key_exists('ar', $decoded) && ! array_key_exists('en', $decoded))) {
            return null;
        }

        return [
            'ar' => (string) ($decoded['ar'] ?? ''),
            'en' => (string) ($decoded['en'] ?? ''),
        ];
    }
};
