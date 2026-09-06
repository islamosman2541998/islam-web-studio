<?php

namespace App\Filament\Resources\MenuItems;

use App\Filament\Resources\StudioResource;
use App\Models\MenuItem;

class MenuItemResource extends StudioResource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $module = 'menu_items';

    protected static ?string $slug = 'menu-items';

    protected static ?int $navigationSort = 8;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
