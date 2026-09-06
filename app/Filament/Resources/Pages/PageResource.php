<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\StudioResource;
use App\Models\Page;

class PageResource extends StudioResource
{
    protected static ?string $model = Page::class;

    protected static ?string $module = 'pages';

    protected static ?string $slug = 'pages';

    protected static ?int $navigationSort = 6;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
