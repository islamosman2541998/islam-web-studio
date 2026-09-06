<?php

namespace App\Filament\Resources\ServiceCategorys;

use App\Filament\Resources\StudioResource;
use App\Models\ServiceCategory;

class ServiceCategoryResource extends StudioResource
{
    protected static ?string $model = ServiceCategory::class;

    protected static ?string $module = 'service_categories';

    protected static ?string $slug = 'service-categories';

    protected static ?int $navigationSort = 2;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
