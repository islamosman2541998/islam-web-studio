<?php

namespace App\Filament\Resources\ProjectCategorys;

use App\Filament\Resources\StudioResource;
use App\Models\ProjectCategory;

class ProjectCategoryResource extends StudioResource
{
    protected static ?string $model = ProjectCategory::class;

    protected static ?string $module = 'project_categories';

    protected static ?string $slug = 'project-categories';

    protected static ?int $navigationSort = 4;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
