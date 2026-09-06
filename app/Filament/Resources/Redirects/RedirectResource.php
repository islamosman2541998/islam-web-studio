<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\StudioResource;
use App\Models\Redirect;

class RedirectResource extends StudioResource
{
    protected static ?string $model = Redirect::class;

    protected static ?string $module = 'redirects';

    protected static ?string $slug = 'redirects';

    protected static ?int $navigationSort = 18;

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
