<?php

namespace App\Filament\Pages;

use App\Models\MenuItem;
use App\Models\MenuLocation;
use App\Support\Studio;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class MenuBuilder extends Page
{
    protected string $view = 'filament.pages.menu-builder';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?int $navigationSort = 1;

    public int $location = 0;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('menu_items.update');
    }

    public static function getNavigationLabel(): string
    {
        return Studio::text('menu_builder');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return Studio::text('navigation');
    }

    public function getTitle(): string
    {
        return self::getNavigationLabel();
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
        $this->location = MenuLocation::first()?->id ?? 0;
    }

    public function items()
    {
        return MenuItem::where('menu_location_id', $this->location)->orderBy('sort_order')->orderBy('id')->get();
    }

    public function saveTree(array $nodes): void
    {
        abort_unless(self::canAccess(), 403);
        validator(['nodes' => $nodes], ['nodes' => 'array|max:300', 'nodes.*.id' => 'required|integer|distinct', 'nodes.*.parent_id' => 'nullable|integer', 'nodes.*.sort_order' => 'required|integer|min:0|max:1000'])->validate();
        $items = MenuItem::where('menu_location_id', $this->location)->get()->keyBy('id');
        $ids = collect($nodes)->pluck('id');
        abort_unless($ids->sort()->values()->all() === $items->keys()->sort()->values()->all(), 422);
        $map = collect($nodes)->keyBy('id');
        foreach ($nodes as $node) {
            $parent = $node['parent_id'];
            $seen = [$node['id']];
            while ($parent) {
                abort_unless(isset($map[$parent]) && ! in_array($parent, $seen) && count($seen) < 5, 422);
                $seen[] = $parent;
                $parent = $map[$parent]['parent_id'];
            }
        }
        DB::transaction(function () use ($nodes, $items) {
            foreach ($nodes as $node) {
                $items[$node['id']]->update(['parent_id' => $node['parent_id'], 'sort_order' => $node['sort_order']]);
            }
        });
        Studio::flush();
        Notification::make()->title(Studio::text('saved'))->success()->send();
    }
}
