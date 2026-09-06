<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Resources\StudioResource;
use App\Models\Project;

class ProjectResource extends StudioResource
{
    protected static ?string $model = Project::class;

    protected static ?string $module = 'projects';

    protected static ?string $slug = 'projects';

    protected static ?int $navigationSort = 5;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
