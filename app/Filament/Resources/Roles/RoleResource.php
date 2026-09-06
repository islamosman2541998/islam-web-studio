<?php

namespace App\Filament\Resources\Roles;

use App\Models\Role;
use App\Support\AccessTable;
use App\Support\Studio;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 71;

    public static function getModelLabel(): string
    {
        return Studio::text('roles');
    }

    public static function getPluralModelLabel(): string
    {
        return self::getModelLabel();
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return Studio::text('access');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('name')->label(Studio::text('name'))->required()->maxLength(60)->unique(ignoreRecord: true)->rules(['not_in:Owner']), Hidden::make('guard_name')->default('web'), CheckboxList::make('permissions')->label(Studio::text('permissions'))->relationship('permissions', 'name')->searchable()->bulkToggleable()->columns(3)->columnSpanFull()]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([ViewColumn::make('card')->label(Studio::text('name'))->view('filament.tables.access-card')->hiddenFrom('md'), TextColumn::make('name')->label(Studio::text('name'))->searchable()->sortable()->visibleFrom('md'), TextColumn::make('permissions_count')->counts('permissions')->label(Studio::text('permissions'))->visibleFrom('md'), TextColumn::make('created_at')->label(Studio::text('field_created_at'))->date()->sortable()->visibleFrom('md')])->filters([AccessTable::dateFilter(), TrashedFilter::make()])->deferFilters(false)->filtersLayout(FiltersLayout::Modal)->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()])->toolbarActions([AccessTable::export('roles')])->paginated([10, 25, 50]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRoles::route('/'), 'create' => Pages\CreateRole::route('/create'), 'edit' => Pages\EditRole::route('/{record}/edit')];
    }
}
