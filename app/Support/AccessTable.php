<?php

namespace App\Support;

use App\Exports\AccessExport;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Maatwebsite\Excel\Facades\Excel;

class AccessTable
{
    public static function dateFilter(): Filter
    {
        return Filter::make('date_range')->label(Studio::text('date_range'))->schema([DatePicker::make('from')->label(Studio::text('from')), DatePicker::make('until')->label(Studio::text('until'))->afterOrEqual('from')])->query(fn ($query, array $data) => $query->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d)));
    }

    public static function export(string $module): Action
    {
        return Action::make('export')->label(Studio::text('export_excel'))->icon('heroicon-o-arrow-down-tray')->action(function ($livewire) use ($module) {
            abort_unless(auth()->user()?->is_active && auth()->user()->hasRole('Owner'), 403);
            $rows = $livewire->getFilteredSortedTableQuery()->limit(10000)->get();
            $data = $rows->map(fn ($row) => $module === 'users' ? [$row->id, $row->name, $row->email, $row->roles->pluck('name')->join(', '), $row->is_active ? '1' : '0', $row->created_at?->toIso8601String()] : [$row->id, $row->name, $row->permissions->pluck('name')->join(', '), $row->created_at?->toIso8601String()]);
            $export = new AccessExport($data, $module);

            return Excel::download($export, $module.'.xlsx');
        });
    }
}
