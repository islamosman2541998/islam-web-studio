<?php

namespace App\Filament\Resources\MenuLocations;

use App\Filament\Resources\StudioResource;
use App\Models\MenuLocation;

class MenuLocationResource extends StudioResource
{
    protected static ?string $model = MenuLocation::class;

    protected static ?string $module = 'menu_locations';

    protected static ?string $slug = 'menu-locations';

    protected static ?int $navigationSort = 7;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
