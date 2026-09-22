<?php

namespace App\Filament\Resources\MetaLeads;

use App\Filament\Resources\StudioResource;
use App\Models\Lead;
use App\Support\ModuleRegistry;
use App\Support\Studio;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class MetaLeadResource extends StudioResource
{
    protected static ?string $model = Lead::class;

    protected static ?string $module = 'leads';

    protected static ?string $slug = 'meta-leads';

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationLabel(): string
    {
        return Studio::text('meta_leads');
    }

    public static function getModelLabel(): string
    {
        return Studio::text('meta_lead');
    }

    public static function getPluralModelLabel(): string
    {
        return Studio::text('meta_leads');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('source', 'meta');
    }

    public static function form(Schema $schema): Schema
    {
        $statuses = collect(['new', 'contacted', 'quoted', 'won', 'lost'])->mapWithKeys(fn ($status) => [$status => Studio::text($status)])->all();

        return $schema->components([
            Section::make(Studio::text('lead_contact_data'))->schema([
                TextInput::make('name')->label(Studio::text('field_name'))->maxLength(255),
                TextInput::make('phone')->label(Studio::text('field_phone'))->tel()->maxLength(255),
                TextInput::make('email')->label(Studio::text('field_email'))->email()->maxLength(255),
                Textarea::make('message')->label(Studio::text('field_message'))->rows(7)->columnSpanFull(),
            ])->columns(3),
            Section::make(Studio::text('lead_ad_attribution'))->schema([
                TextInput::make('meta_campaign_name')->label(Studio::text('field_meta_campaign_name'))->disabled(),
                TextInput::make('meta_adset_name')->label(Studio::text('field_meta_adset_name'))->disabled(),
                TextInput::make('meta_ad_name')->label(Studio::text('field_meta_ad_name'))->disabled(),
                TextInput::make('meta_platform')->label(Studio::text('field_meta_platform'))->disabled(),
                TextInput::make('meta_campaign_id')->label(Studio::text('field_meta_campaign_id'))->disabled(),
                TextInput::make('meta_adset_id')->label(Studio::text('field_meta_adset_id'))->disabled(),
                TextInput::make('meta_ad_id')->label(Studio::text('field_meta_ad_id'))->disabled(),
                TextInput::make('meta_form_id')->label(Studio::text('field_meta_form_id'))->disabled(),
                DateTimePicker::make('meta_created_at')->label(Studio::text('field_meta_created_at'))->disabled()->seconds(false),
            ])->columns(3)->collapsible(),
            Section::make(Studio::text('lead_follow_up'))->schema([
                Select::make('status')->label(Studio::text('field_status'))->options($statuses)->required(),
                DateTimePicker::make('follow_up_at')->label(Studio::text('field_follow_up_at'))->seconds(false),
                Textarea::make('internal_notes')->label(Studio::text('field_internal_notes'))->rows(5)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        $statuses = collect(['new', 'contacted', 'quoted', 'won', 'lost'])->mapWithKeys(fn ($status) => [$status => Studio::text($status)])->all();

        return $table
            ->columns([
                TextColumn::make('name')->label(Studio::text('field_name'))->searchable()->sortable()->weight('semibold')->wrap(),
                TextColumn::make('phone')->label(Studio::text('field_phone'))->searchable()->copyable()->url(fn ($record) => $record->phone ? 'tel:'.$record->phone : null),
                TextColumn::make('email')->label(Studio::text('field_email'))->searchable()->copyable()->toggleable(),
                TextColumn::make('meta_campaign_name')->label(Studio::text('field_meta_campaign_name'))->searchable()->wrap()->toggleable(),
                TextColumn::make('meta_ad_name')->label(Studio::text('field_meta_ad_name'))->searchable()->wrap()->toggleable(),
                TextColumn::make('meta_adset_name')->label(Studio::text('field_meta_adset_name'))->searchable()->wrap()->toggleable(isToggledHiddenByDefault: true),
                SelectColumn::make('status')->label(Studio::text('field_status'))->options($statuses)
                    ->disabled(fn () => ! ModuleRegistry::permission('leads', 'update'))
                    ->updateStateUsing(function ($record, $state) use ($statuses) {
                        abort_unless(ModuleRegistry::permission('leads', 'update') && isset($statuses[$state]), 403);
                        $record->update(['status' => $state]);

                        return $state;
                    }),
                TextColumn::make('meta_created_at')->label(Studio::text('field_meta_created_at'))->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('message')->label(Studio::text('field_message'))->limit(70)->wrap()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->label(Studio::text('field_status'))->options($statuses)->multiple(),
                SelectFilter::make('meta_campaign_id')->label(Studio::text('field_meta_campaign_name'))
                    ->options(fn () => Lead::query()->where('source', 'meta')->whereNotNull('meta_campaign_id')->pluck('meta_campaign_name', 'meta_campaign_id')->filter()->all())->searchable()->multiple(),
                SelectFilter::make('meta_platform')->label(Studio::text('field_meta_platform'))
                    ->options(fn () => Lead::query()->where('source', 'meta')->whereNotNull('meta_platform')->distinct()->pluck('meta_platform', 'meta_platform')->all()),
                Filter::make('needs_follow_up')->label(Studio::text('needs_follow_up'))->query(fn (Builder $query) => $query->whereNotIn('status', ['won', 'lost'])),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()])
            ->defaultSort('meta_created_at', 'desc')->paginated([10, 25, 50, 100])->striped()
            ->poll('30s')->emptyStateHeading(Studio::text('no_records'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMetaLeads::route('/'),
            'edit' => Pages\EditMetaLead::route('/{record}/edit'),
        ];
    }
}
