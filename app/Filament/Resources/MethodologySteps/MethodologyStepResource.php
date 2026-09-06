<?php

namespace App\Filament\Resources\MethodologySteps;

use App\Filament\Resources\StudioResource;
use App\Models\MethodologyStep;

class MethodologyStepResource extends StudioResource
{
    protected static ?string $model = MethodologyStep::class;

    protected static ?string $module = 'methodology_steps';

    protected static ?string $slug = 'methodology-steps';

    protected static ?int $navigationSort = 15;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
