<?php

namespace App\Support;

use App\Jobs\BuildExport;
use App\Models\ExportRun;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ResourceTable
{
    public static function make(Table $table, string $module): Table
    {
        $d = ModuleRegistry::get($module);
        $fields = $d['fields'];
        $title = $d['title'];
        $locale = app()->getLocale();
        $readonly = $d['readonly'] ?? false;
        $columns = [ViewColumn::make('mobile_card')->view('filament.tables.mobile-card')->hiddenFrom('md'), TextColumn::make('id')->label('#')->sortable()->visibleFrom('md')->toggleable(isToggledHiddenByDefault: true), TextColumn::make($title)->label(Studio::text('field_'.$title))->getStateUsing(fn ($record) => $record->titleText())->searchable(query: function (Builder $query, string $search) use ($title, $fields) {
            $query->where(function (Builder $query) use ($title, $fields, $search) {
                if ($fields[$title]['translated'] ?? false) {
                    $query->where($title.'->ar', 'like', '%'.$search.'%')->orWhere($title.'->en', 'like', '%'.$search.'%');
                } else {
                    $query->where($title, 'like', '%'.$search.'%');
                }foreach (['email', 'phone', 'client'] as $extra) {
                    if (isset($fields[$extra])) {
                        $query->orWhere($extra, 'like', '%'.$search.'%');
                    }
                }
            });
        })->sortable(query: fn (Builder $query, string $direction) => $query->orderBy($title.(($fields[$title]['translated'] ?? false) ? '->'.$locale : ''), $direction))->wrap()->visibleFrom('md')];
        if ($module === 'assets') {
            array_unshift($columns, ImageColumn::make('thumbnail')->label(Studio::text('preview'))->getStateUsing(fn ($record) => $record->kind === 'image' && $record->visibility === 'public' ? $record->imageUrl(320) : null)->imageSize(64)->visibleFrom('md'));
            $columns[] = TextColumn::make('processing_state')->label(Studio::text('field_status'))->getStateUsing(fn ($record) => Studio::text(($record->metadata['error'] ?? null) ? 'failed' : ($record->upload_path ? 'processing' : 'completed')))->badge()->visibleFrom('md');
        }
        $filters = [];
        foreach ($fields as $name => $field) {
            if ($field['type'] === 'boolean') {
                if (in_array($name, ['is_active', 'is_featured', 'is_approved'])) {
                    $columns[] = ToggleColumn::make($name)->label(Studio::text('field_'.$name))->disabled(fn () => ! ModuleRegistry::permission($module, 'update'))->updateStateUsing(function ($record, $state) use ($module, $name) {
                        abort_unless(ModuleRegistry::permission($module, 'update'), 403);
                        $record->update([$name => (bool) $state]);
                        Notification::make()->success()->title(Studio::text('updated'))->send();

                        return (bool) $state;
                    })->visibleFrom('md');
                }
                $filters[] = TernaryFilter::make($name)->label(Studio::text('field_'.$name));
            }
            if ($field['type'] === 'select') {
                $options = collect($field['options'])->mapWithKeys(fn ($v) => [$v => Studio::text($v)])->all();
                $filters[] = SelectFilter::make($name)->label(Studio::text('field_'.$name))->options($options)->multiple();
                if ($name === 'status') {
                    $columns[] = $readonly ? TextColumn::make('status')->label(Studio::text('field_status'))->badge()->formatStateUsing(fn ($state) => Studio::text($state))->visibleFrom('md') : SelectColumn::make('status')->label(Studio::text('field_status'))->options($options)->disabled(fn () => ! ModuleRegistry::permission($module, 'update'))->updateStateUsing(function ($record, $state) use ($module, $options) {
                        abort_unless(ModuleRegistry::permission($module, 'update') && isset($options[$state]), 403);
                        $record->update(['status' => $state]);
                        Notification::make()->success()->title(Studio::text('updated'))->send();

                        return $state;
                    })->visibleFrom('md');
                }
            }
            if ($field['type'] === 'relation') {
                $filters[] = SelectFilter::make($name)->label(Studio::text('field_'.$name))->options(fn () => ModuleRegistry::options($field['model']))->searchable()->multiple();
            }
        }
        $filters[] = Filter::make('created_range')->label(Studio::text('date_range'))->schema([DatePicker::make('from')->label(Studio::text('from')), DatePicker::make('until')->label(Studio::text('until'))->afterOrEqual('from')])->query(fn (Builder $query, array $data) => $query->when($data['from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))->when($data['until'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date)))->indicateUsing(fn (array $data) => array_filter([isset($data['from']) ? Studio::text('from').': '.$data['from'] : null, isset($data['until']) ? Studio::text('until').': '.$data['until'] : null]));
        $filters[] = TrashedFilter::make();
        if (isset($fields['rating'])) {
            $filters[] = SelectFilter::make('rating')->label(Studio::text('field_rating'))->options(array_combine(range(1, 5), range(1, 5)));
        }
        $columns[] = TextColumn::make('created_at')->label(Studio::text('field_created_at'))->dateTime('Y-m-d H:i')->sortable()->visibleFrom('md')->toggleable();
        $actions = $readonly ? [] : [EditAction::make(), DeleteAction::make(), RestoreAction::make()];
        if ($module === 'export_runs') {
            $actions[] = Action::make('download')->label(Studio::text('download'))->icon('heroicon-o-arrow-down-tray')->visible(fn ($record) => $record->status === 'completed')->url(fn ($record) => route('exports.download', $record));
        }
        if (isset($d['public'])) {
            $actions[] = Action::make('preview')->label(Studio::text('preview'))->icon('heroicon-o-arrow-top-right-on-square')->url(fn ($record) => $record->publicUrl())->openUrlInNewTab();
        }
        if (! $readonly && (isset($fields['status']) || isset($fields['is_active']))) {
            $schema = [];
            foreach (['status', 'is_active', 'is_featured', 'is_approved'] as $field) {
                if (isset($fields[$field])) {
                    $schema[] = ResourceSchema::field($module, $field, $fields[$field]);
                }
            }
            $actions[] = Action::make('quick_update')->label(Studio::text('quick_update'))->icon('heroicon-o-adjustments-horizontal')->visible(fn () => ModuleRegistry::permission($module, 'update'))->schema($schema)->fillForm(fn ($record) => $record->only(['status', 'is_active', 'is_featured', 'is_approved']))->action(function ($record, array $data) use ($module) {
                abort_unless(ModuleRegistry::permission($module, 'update'), 403);
                $record->update($data);
                Notification::make()->success()->title(Studio::text('updated'))->send();
            });
        }
        $bulk = [];
        if (! $readonly) {
            $bulk = [DeleteBulkAction::make()->authorizeIndividualRecords('delete'), RestoreBulkAction::make()->authorizeIndividualRecords('restore')];
            if (isset($fields['status'])) {
                $bulk[] = BulkAction::make('status')->label(Studio::text('change_status'))->visible(fn () => ModuleRegistry::permission($module, 'update'))->schema([ResourceSchema::field($module, 'status', $fields['status'])->required()])->action(function (Collection $records, array $data) use ($module, $fields) {
                    abort_unless(ModuleRegistry::permission($module, 'update') && in_array($data['status'], $fields['status']['options']), 403);
                    $records->each(fn ($record) => $record->update(['status' => $data['status']]));
                    Notification::make()->success()->title(Studio::text('updated'))->send();
                })->deselectRecordsAfterCompletion();
            }
        }
        $toolbar = $bulk ? [BulkActionGroup::make($bulk)] : [];
        if (! $readonly) {
            $toolbar[] = Action::make('export')->label(Studio::text('export_excel'))->icon('heroicon-o-arrow-down-tray')->visible(fn () => ModuleRegistry::permission($module, 'export'))->schema([Select::make('locale')->label(Studio::text('language'))->options(['ar' => 'العربية', 'en' => 'English', 'both' => 'العربية + English'])->default('both')->required()])->action(function (array $data, $livewire) use ($module) {
                abort_unless(ModuleRegistry::permission($module, 'export'), 403);
                $query = $livewire->getFilteredSortedTableQuery();
                $count = (clone $query)->count();
                if ($count > 50000) {
                    Notification::make()->danger()->title(Studio::text('export_too_large'))->send();

                    return;
                }$ids = $query->pluck((new (ModuleRegistry::model($module)))->getTable().'.id')->all();
                $run = ExportRun::create(['user_id' => auth()->id(), 'module' => $module, 'ids' => $ids, 'columns' => array_keys(ModuleRegistry::get($module)['fields']), 'locale' => $data['locale'], 'status' => 'queued', 'row_count' => count($ids)]);
                BuildExport::dispatch($run->id);
                Notification::make()->success()->title(Studio::text('export_queued'))->body(Studio::text('export_help'))->send();
            });
        }
        $table->columns($columns)->filters($filters)->filtersLayout(FiltersLayout::Modal)->deferFilters(false)->searchDebounce('400ms')->defaultSort('id', 'desc')->recordActions($actions)->toolbarActions($toolbar)->paginated([10, 25, 50, 100])->striped()->emptyStateHeading(Studio::text('no_records'))->recordUrl($readonly ? null : fn ($record) => $table->getLivewire()::getResource()::getUrl('edit', ['record' => $record]))->poll('30s');
        if (isset($fields['sort_order']) && ! $readonly) {
            $table->reorderable('sort_order', fn () => ModuleRegistry::permission($module, 'update'));
        }

        return $table;
    }
}
