<?php

namespace App\Filament\Resources\Users;

use App\Models\User;
use App\Support\AccessTable;
use App\Support\MediaPicker;
use App\Support\Studio;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 70;

    public static function getModelLabel(): string
    {
        return Studio::text('users');
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
        return $schema->components([
            TextInput::make('name')->label(Studio::text('name'))->required()->maxLength(255), TextInput::make('email')->label(Studio::text('email'))->email()->required()->unique(ignoreRecord: true)->maxLength(255), TextInput::make('password')->label(Studio::text('password'))->password()->revealable()->minLength(12)->required(fn (string $operation) => $operation === 'create')->dehydrated(fn ($state) => filled($state)), MediaPicker::make('avatar_media_id', ['image'])->label(Studio::text('field_avatar_media_id')), Select::make('roles')->label(Studio::text('roles'))->relationship('roles', 'name')->multiple()->preload()->required(), Select::make('locale')->label(Studio::text('language'))->options(['ar' => 'العربية', 'en' => 'English'])->default('ar')->required(), Toggle::make('is_active')->label(Studio::text('field_is_active'))->default(true)->disabled(fn (?User $record) => $record?->hasRole('Owner') ?? false)->helperText(Studio::text('owner_protected')),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([ViewColumn::make('card')->label(Studio::text('name'))->view('filament.tables.access-card')->hiddenFrom('md'), TextColumn::make('name')->label(Studio::text('name'))->searchable()->sortable()->wrap()->visibleFrom('md'), TextColumn::make('email')->label(Studio::text('email'))->searchable()->sortable()->wrap()->visibleFrom('md'), TextColumn::make('roles.name')->label(Studio::text('roles'))->badge()->visibleFrom('md'), ToggleColumn::make('is_active')->label(Studio::text('field_is_active'))->disabled(fn ($record) => $record->hasRole('Owner'))->updateStateUsing(function ($record, $state) {
            abort_unless(auth()->user()?->hasRole('Owner') && ! $record->hasRole('Owner'), 403);
            $record->update(['is_active' => (bool) $state]);
            Notification::make()->success()->title(Studio::text('updated'))->send();

            return (bool) $state;
        }), TextColumn::make('created_at')->label(Studio::text('field_created_at'))->date()->sortable()->visibleFrom('md')])->filters([SelectFilter::make('roles')->label(Studio::text('roles'))->relationship('roles', 'name'), TernaryFilter::make('is_active')->label(Studio::text('field_is_active')), AccessTable::dateFilter(), TrashedFilter::make()])->filtersLayout(FiltersLayout::Modal)->deferFilters(false)->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->authorizeIndividualRecords('delete'), RestoreBulkAction::make()->authorizeIndividualRecords('restore')]), AccessTable::export('users')])->paginated([10, 25, 50]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class])->with('roles');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListUsers::route('/'), 'create' => Pages\CreateUser::route('/create'), 'edit' => Pages\EditUser::route('/{record}/edit')];
    }
}
