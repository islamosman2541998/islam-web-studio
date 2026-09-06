<?php

namespace App\Filament\Resources\ExportRuns;

use App\Filament\Resources\StudioResource;
use App\Models\ExportRun;

class ExportRunResource extends StudioResource
{
    protected static ?string $model = ExportRun::class;

    protected static ?string $module = 'export_runs';

    protected static ?string $slug = 'export-runs';

    protected static ?int $navigationSort = 19;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/')];
    }
}
