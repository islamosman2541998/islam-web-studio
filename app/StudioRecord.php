<?php

namespace App;

use App\Jobs\GenerateAssetConversions;
use App\Models\Asset;
use App\Models\Page;
use App\Support\MediaPipeline;
use App\Support\ModuleRegistry;
use App\Support\Studio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

abstract class StudioRecord extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $guarded = ['id', 'slug_ar', 'slug_en'];

    public array $translatable = [];

    public function definition(): array
    {
        return ModuleRegistry::get($this->getTable());
    }

    protected function casts(): array
    {
        $casts = [];
        foreach ($this->definition()['fields'] as $field => $definition) {
            if ($definition['translated'] ?? false) {
                $casts[$field] = 'array';
            } elseif (in_array($definition['type'], ['json', 'tags', 'dynamic_items'])) {
                $casts[$field] = 'array';
            } elseif ($definition['type'] === 'boolean') {
                $casts[$field] = 'boolean';
            } elseif ($definition['type'] === 'integer') {
                $casts[$field] = 'integer';
            } elseif ($definition['type'] === 'datetime') {
                $casts[$field] = 'datetime';
            }
        }

        return $casts;
    }

    protected static function booted(): void
    {
        static::saved(function (self $record): void {
            Studio::flush();
            if ($record instanceof Asset && $record->upload_path && ($record->wasRecentlyCreated || $record->wasChanged('upload_path'))) {
                app(MediaPipeline::class)->ingest($record);
                if ($record->kind === 'image') {
                    GenerateAssetConversions::dispatch($record->id)->afterResponse();
                }
            }
        });
        static::deleted(fn () => Studio::flush());
        static::restored(fn () => Studio::flush());
    }

    public function text(string $field, ?string $locale = null): string
    {
        if (in_array($field, $this->translatable)) {
            return (string) $this->getTranslation($field, $locale ?? app()->getLocale(), false);
        }

        return (string) ($this->getAttribute($field) ?? '');
    }

    public function titleText(?string $locale = null): string
    {
        return $this->text($this->definition()['title'], $locale);
    }

    public function scopePublished(Builder $query, ?string $locale = null): Builder
    {
        $fields = $this->definition()['fields'];
        if (isset($fields['status']) && $this->getTable() !== 'leads') {
            $query->where('status', 'published');
        }
        if (isset($fields['is_active'])) {
            $query->where('is_active', true);
        }
        if (isset($fields['is_approved'])) {
            $query->where('is_approved', true);
        }
        if (isset($fields['published_at'])) {
            $query->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
        }
        $title = $this->definition()['title'];
        if (($fields[$title]['translated'] ?? false)) {
            $query->whereNotNull($title.'->'.($locale ?? app()->getLocale()))->where($title.'->'.($locale ?? app()->getLocale()), '!=', '');
        }

        return $query;
    }

    public function publicUrl(string $locale = ''): string
    {
        $locale = $locale ?: app()->getLocale();
        $route = $this->definition()['public'] ?? null;
        if ($this instanceof Page && in_array($this->template, ['home', 'about'])) {
            return route($this->template, ['locale' => $locale]);
        }

        return $route ? route($route, ['locale' => $locale, 'slug' => $this->text('slug', $locale)]) : '#';
    }
}
