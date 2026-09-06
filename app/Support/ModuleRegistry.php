<?php

namespace App\Support;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Model;

class ModuleRegistry
{
    public static function all(): array
    {
        return config('studio.modules', []);
    }

    public static function get(string $key): array
    {
        return self::all()[$key] ?? throw new \InvalidArgumentException('Unknown module.');
    }

    public static function model(string $key): string
    {
        return 'App\\Models\\'.self::get($key)['model'];
    }

    public static function label(string $key): string
    {
        $definition = self::get($key);

        return $definition[app()->getLocale()] ?? $definition['en'] ?? $key;
    }

    public static function options(string $model, bool $publicOnly = false): array
    {
        $class = 'App\\Models\\'.$model;
        $query = $class::query();
        if ($publicOnly && is_subclass_of($class, StudioRecord::class)) {
            $query->published();
        }

        return $query->limit(500)->get()->mapWithKeys(fn (Model $record) => [$record->id => $record instanceof StudioRecord ? ($record->titleText() ?: $record->titleText('en')) : $record->name])->all();
    }

    public static function permission(string $module, string $action): bool
    {
        return (bool) (auth()->user()?->is_active && auth()->user()->can($module.'.'.$action));
    }
}
