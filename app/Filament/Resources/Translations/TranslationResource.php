<?php

namespace App\Filament\Resources\Translations;

use App\Filament\Resources\StudioResource;
use App\Models\Translation;

class TranslationResource extends StudioResource
{
    protected static ?string $model = Translation::class;

    protected static ?string $module = 'translations';

    protected static ?string $slug = 'translations';

    protected static ?int $navigationSort = 17;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecords::route('/'),
            'create' => Pages\CreateRecord::route('/create'),
            'edit' => Pages\EditRecord::route('/{record}/edit'),
        ];
    }
}
