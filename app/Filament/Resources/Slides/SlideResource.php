<?php

namespace App\Filament\Resources\Slides;

use App\Filament\Resources\StudioResource;
use App\Models\Slide;

class SlideResource extends StudioResource
{
    protected static ?string $model = Slide::class;

    protected static ?string $module = 'slides';

    protected static ?string $slug = 'slides';

    protected static ?int $navigationSort = 10;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
