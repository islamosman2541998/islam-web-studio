<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\StudioResource;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;

class LeadResource extends StudioResource
{
    protected static ?string $model = Lead::class;

    protected static ?string $module = 'leads';

    protected static ?string $slug = 'leads';

    protected static ?int $navigationSort = 16;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function (Builder $query): void {
            $query->whereNull('source')->orWhere('source', '!=', 'meta');
        });
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
