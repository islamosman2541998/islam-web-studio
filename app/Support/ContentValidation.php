<?php

namespace App\Support;

use App\Models\MenuItem;
use App\Models\Page;
use App\StudioRecord;
use Illuminate\Validation\ValidationException;

class ContentValidation
{
    public static function prepare(string $module, array $data, ?StudioRecord $record = null): array
    {
        if ($module === 'menu_items') {
            if (($data['type'] ?? null) === 'route' && ! array_key_exists($data['route_name'] ?? '', MenuResolver::routeOptions())) {
                throw ValidationException::withMessages(['data.route_name' => Studio::text('invalid_link')]);
            }
            if ($record && (int) $record->menu_location_id !== (int) ($data['menu_location_id'] ?? 0) && MenuItem::where('parent_id', $record->id)->exists()) {
                throw ValidationException::withMessages(['data.menu_location_id' => Studio::text('menu_move_children')]);
            }
            $parentId = $data['parent_id'] ?? null;
            $seen = [];
            while ($parentId) {
                $parent = MenuItem::find($parentId);
                if (! $parent || (int) $parent->menu_location_id !== (int) ($data['menu_location_id'] ?? 0) || in_array($parentId, $seen) || ($record && (int) $parentId === $record->id)) {
                    throw ValidationException::withMessages(['data.parent_id' => Studio::text('invalid_parent')]);
                } $seen[] = $parentId;
                $parentId = $parent->parent_id;
                if (count($seen) > 4) {
                    throw ValidationException::withMessages(['data.parent_id' => Studio::text('invalid_parent')]);
                }
            }
        }
        if ($module === 'pages') {
            $reserved = ['services', 'work', 'journal', 'about', 'process', 'testimonials', 'contact', 'admin'];
            foreach (['ar', 'en'] as $locale) {
                if (in_array($data['slug'][$locale] ?? '', $reserved)) {
                    throw ValidationException::withMessages(['data.slug.'.$locale => Studio::text('reserved_slug')]);
                }
            }
            if (in_array($data['template'] ?? '', ['home', 'about']) && Page::where('template', $data['template'])->when($record, fn ($query) => $query->whereKeyNot($record->id))->exists()) {
                throw ValidationException::withMessages(['data.template' => Studio::text('unique_template')]);
            }
        }
        if ($module === 'redirects') {
            foreach (['from_path', 'to_path'] as $field) {
                if (! preg_match('~^/(ar|en)/~', $data[$field] ?? '')) {
                    throw ValidationException::withMessages(['data.'.$field => Studio::text('redirect_help')]);
                }
            } if ($data['from_path'] === $data['to_path']) {
                throw ValidationException::withMessages(['data.to_path' => Studio::text('invalid_link')]);
            }
        }

        return $data;
    }
}
