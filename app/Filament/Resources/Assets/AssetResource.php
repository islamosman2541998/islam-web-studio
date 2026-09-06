<?php

namespace App\Filament\Resources\Assets;

use App\Filament\Resources\StudioResource;
use App\Models\Asset;

class AssetResource extends StudioResource
{
    protected static ?string $model = Asset::class;

    protected static ?string $module = 'assets';

    protected static ?string $slug = 'assets';

    protected static ?int $navigationSort = 1;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
