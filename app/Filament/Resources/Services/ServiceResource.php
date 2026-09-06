<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\StudioResource;
use App\Models\Service;

class ServiceResource extends StudioResource
{
    protected static ?string $model = Service::class;

    protected static ?string $module = 'services';

    protected static ?string $slug = 'services';

    protected static ?int $navigationSort = 3;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
