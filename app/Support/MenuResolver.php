<?php

namespace App\Support;

use App\Models\MenuItem;
use App\Models\MenuLocation;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;

class MenuResolver
{
    public static function routeOptions(): array
    {
        $routes = ['home', 'services.index', 'projects.index', 'about', 'methodology', 'posts.index', 'testimonials', 'contact'];

        return collect($routes)->mapWithKeys(fn ($name) => [$name => Studio::text('route_'.$name)])->all();
    }

    public static function forLocation(string $location): array
    {
        $locale = app()->getLocale();
        $items = Cache::remember('studio.menu.'.Cache::memo()->get('studio.menu.version', 'initial').'.'.$locale.'.'.$location, 3600, function () use ($location, $locale) {
            $menu = MenuLocation::where('key', $location)->where('is_active', true)->first();
            if (! $menu) {
                return [];
            }$all = MenuItem::where('menu_location_id', $menu->id)->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

            return self::branch($all, null, $locale, []);
        });
        $filter = function (array $items) use (&$filter): array {
            return array_values(array_map(function ($item) use (&$filter) {
                $item['children'] = $filter($item['children']);

                return $item;
            }, array_filter($items, fn ($item) => $item['visibility'] === 'all' || auth()->check())));
        };

        return $filter($items);
    }

    private static function branch($all, ?int $parent, string $locale, array $seen): array
    {
        if (count($seen) > 5) {
            return [];
        }$result = [];
        foreach ($all->where('parent_id', $parent) as $item) {
            if (in_array($item->id, $seen) || ! $item->text('title', $locale)) {
                continue;
            }$children = self::branch($all, $item->id, $locale, [...$seen, $item->id]);
            $url = '#';
            if ($item->type === 'route') {
                if (! array_key_exists($item->route_name, self::routeOptions())) {
                    continue;
                }$url = route($item->route_name, ['locale' => $locale]);
            } elseif ($item->type === 'external') {
                $url = Studio::safeUrl($item->url);
            } elseif ($item->type === 'page') {
                $page = Page::published($locale)->find($item->page_id);
                if (! $page) {
                    continue;
                }$url = $page->publicUrl($locale);
            } elseif ($item->type === 'dynamic_group') {
                $source = $item->dynamic_source;
                $d = ModuleRegistry::all()[$source] ?? null;
                if (! $d || ! isset($d['public'])) {
                    continue;
                }$class = ModuleRegistry::model($source);
                $query = $class::published($locale);
                if ($item->dynamic_mode === 'selected') {
                    $query->whereIn('id', $item->dynamic_items ?? []);
                } else {
                    if ($item->dynamic_featured && isset($d['fields']['is_featured'])) {
                        $query->where('is_featured', true);
                    }foreach (['service_category_id', 'project_category_id', 'post_category_id'] as $field) {
                        if ($item->dynamic_category_id && isset($d['fields'][$field])) {
                            $query->where($field, $item->dynamic_category_id);
                        }
                    }
                }
                foreach ($query->orderBy('sort_order')->orderBy('id')->limit(min(50, max(1, $item->dynamic_limit ?? 8)))->get() as $record) {
                    $children[] = ['title' => $record->titleText($locale), 'url' => $record->publicUrl($locale), 'children' => [], 'blank' => false, 'visibility' => 'all'];
                }
                if (! $children) {
                    continue;
                }$index = match ($source) {
                    'services' => 'services.index','projects' => 'projects.index','posts' => 'posts.index',default => null
                };
                $url = $index ? route($index, ['locale' => $locale]) : '#';
            }
            $result[] = ['title' => $item->text('title', $locale), 'url' => $url, 'children' => $children, 'blank' => (bool) $item->target_blank, 'visibility' => $item->visibility ?? 'all'];
        }

return $result;
    }
}
