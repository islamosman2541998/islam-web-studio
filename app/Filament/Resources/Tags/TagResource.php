<?php

namespace App\Filament\Resources\Tags;

use App\Filament\Resources\StudioResource;
use App\Models\Tag;

class TagResource extends StudioResource
{
    protected static ?string $model = Tag::class;

    protected static ?string $module = 'tags';

    protected static ?string $slug = 'tags';

    protected static ?int $navigationSort = 13;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
