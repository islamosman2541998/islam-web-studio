<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, list<string>> */
    private const TRANSLATIONS = [
        'what_we_do' => ['home.services_label'],
        'services_title' => ['home.services_title', 'page_headers.services_title'],
        'services_intro' => ['home.services_description', 'page_headers.services_description'],
        'route_services.index' => ['page_headers.services_label'],
        'selected_work' => ['home.projects_label'],
        'projects_title' => ['home.projects_title', 'page_headers.projects_title'],
        'projects_intro' => ['home.projects_description', 'page_headers.projects_description'],
        'route_projects.index' => ['page_headers.projects_label'],
        'our_process' => ['home.process_label'],
        'methodology_title' => ['home.process_title', 'page_headers.methodology_title'],
        'methodology_intro' => ['home.process_description', 'page_headers.methodology_description'],
        'route_methodology' => ['page_headers.methodology_label'],
        'client_words' => ['home.testimonials_label'],
        'testimonials_title' => ['home.testimonials_title', 'page_headers.testimonials_title'],
        'testimonials_intro' => ['home.testimonials_description', 'page_headers.testimonials_description'],
        'route_testimonials' => ['page_headers.testimonials_label'],
        'insights' => ['home.posts_label'],
        'posts_title' => ['home.posts_title', 'page_headers.posts_title'],
        'posts_intro' => ['home.posts_description', 'page_headers.posts_description'],
        'route_posts.index' => ['page_headers.posts_label'],
        'contact_title' => ['page_headers.contact_title'],
        'contact_intro' => ['page_headers.contact_description'],
        'route_contact' => ['page_headers.contact_label'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('translations')) {
            return;
        }

        foreach (self::TRANSLATIONS as $translationKey => $settingKeys) {
            $existing = DB::table('translations')->where('key', $translationKey)->first();

            if ($existing) {
                if ($existing->deleted_at !== null || ! $existing->is_active) {
                    DB::table('translations')->where('key', $translationKey)->update([
                        'deleted_at' => null,
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
                }

                continue;
            }

            DB::table('translations')->insert([
                'key' => $translationKey,
                'value' => $this->encode($this->firstTranslatedSetting($settingKeys) ?? [
                    'ar' => trans('studio.'.$translationKey, [], 'ar'),
                    'en' => trans('studio.'.$translationKey, [], 'en'),
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->whereIn('key', collect(self::TRANSLATIONS)->flatten()->all())
                ->delete();
        }
    }

    public function down(): void
    {
        // Interface copy is deliberately preserved when rolling back.
    }

    /** @param list<string> $keys */
    private function firstTranslatedSetting(array $keys): ?array
    {
        if (! Schema::hasTable('settings')) {
            return null;
        }

        foreach ($keys as $key) {
            $value = DB::table('settings')->where('key', $key)->value('value');
            $decoded = is_string($value) ? json_decode($value, true) : $value;

            if (is_array($decoded) && (array_key_exists('ar', $decoded) || array_key_exists('en', $decoded))) {
                return [
                    'ar' => (string) ($decoded['ar'] ?? ''),
                    'en' => (string) ($decoded['en'] ?? ''),
                ];
            }
        }

        return null;
    }

    private function encode(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
};
