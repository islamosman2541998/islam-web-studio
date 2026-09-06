<?php

namespace App\Filament\Resources;

use App\Support\ModuleRegistry;
use App\Support\ResourceSchema;
use App\Support\ResourceTable;
use App\Support\Studio;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

abstract class StudioResource extends Resource
{
    protected static ?string $module = null;

    protected static bool $isGloballySearchable = false;

    public static function module(): string
    {
        return static::$module;
    }

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return match (static::module()) {
            'assets' => 'heroicon-o-photo',
            'service_categories' => 'heroicon-o-folder',
            'services' => 'heroicon-o-wrench-screwdriver',
            'project_categories' => 'heroicon-o-rectangle-group',
            'projects' => 'heroicon-o-computer-desktop',
            'pages' => 'heroicon-o-document-text',
            'menu_locations' => 'heroicon-o-map-pin',
            'menu_items' => 'heroicon-o-bars-3',
            'sliders' => 'heroicon-o-rectangle-stack',
            'slides' => 'heroicon-o-play-circle',
            'post_categories' => 'heroicon-o-folder-open',
            'posts' => 'heroicon-o-newspaper',
            'tags' => 'heroicon-o-tag',
            'testimonials' => 'heroicon-o-chat-bubble-left-right',
            'methodology_steps' => 'heroicon-o-list-bullet',
            'leads' => 'heroicon-o-inbox-arrow-down',
            'translations' => 'heroicon-o-language',
            'redirects' => 'heroicon-o-arrow-path-rounded-square',
            'export_runs' => 'heroicon-o-document-arrow-down',
            default => 'heroicon-o-circle-stack',
        };
    }

    public static function getModelLabel(): string
    {
        return ModuleRegistry::label(static::module());
    }

    public static function getPluralModelLabel(): string
    {
        return static::getModelLabel();
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return Studio::text(ModuleRegistry::get(static::module())['group']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(ResourceSchema::make(static::module()))->columns(1);
    }

    public static function table(Table $table): Table
    {
        return ResourceTable::make($table, static::module());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
        if (static::module() === 'export_runs') {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }
}
