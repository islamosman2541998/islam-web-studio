<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\StudioResource;
use App\Models\Post;

class PostResource extends StudioResource
{
    protected static ?string $model = Post::class;

    protected static ?string $module = 'posts';

    protected static ?string $slug = 'posts';

    protected static ?int $navigationSort = 12;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
