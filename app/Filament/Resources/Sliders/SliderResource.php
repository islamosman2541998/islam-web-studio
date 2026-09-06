<?php

namespace App\Filament\Resources\Sliders;

use App\Filament\Resources\StudioResource;
use App\Models\Slider;

class SliderResource extends StudioResource
{
    protected static ?string $model = Slider::class;

    protected static ?string $module = 'sliders';

    protected static ?string $slug = 'sliders';

    protected static ?int $navigationSort = 9;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
