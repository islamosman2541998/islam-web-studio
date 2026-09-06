<?php

namespace App\Filament\Resources\PostCategorys;

use App\Filament\Resources\StudioResource;
use App\Models\PostCategory;

class PostCategoryResource extends StudioResource
{
    protected static ?string $model = PostCategory::class;

    protected static ?string $module = 'post_categories';

    protected static ?string $slug = 'post-categories';

    protected static ?int $navigationSort = 11;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
