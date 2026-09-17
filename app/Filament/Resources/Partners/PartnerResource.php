<?php

namespace App\Filament\Resources\Partners;

use App\Filament\Resources\StudioResource;
use App\Models\Partner;

class PartnerResource extends StudioResource
{
    protected static ?string $model = Partner::class;

    protected static ?string $module = 'partners';

    protected static ?string $slug = 'partners';

    protected static ?int $navigationSort = 10;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
