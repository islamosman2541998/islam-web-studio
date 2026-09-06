<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\StudioResource;
use App\Models\Testimonial;

class TestimonialResource extends StudioResource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $module = 'testimonials';

    protected static ?string $slug = 'testimonials';

    protected static ?int $navigationSort = 14;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
