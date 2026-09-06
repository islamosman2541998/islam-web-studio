<?php

namespace App\Exports;

use App\Models\ExportRun;
use App\Support\ModuleRegistry;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class RecordsExport extends StringValueBinder implements FromQuery, WithCustomValueBinder, WithHeadings, WithMapping
{
    public function __construct(public ExportRun $run) {}

    public function query(): Builder|EloquentBuilder|Relation
    {
        $class = ModuleRegistry::model($this->run->module);

        return $class::withTrashed()->whereIn('id', $this->run->ids)->orderBy('id');
    }

    public function exportColumns(): array
    {
        return array_values(array_diff(array_intersect($this->run->columns, array_keys(ModuleRegistry::get($this->run->module)['fields'])), ['upload_path', 'metadata', 'ids', 'columns', 'file_path']));
    }

    public function headings(): array
    {
        $headings = ['ID'];
        $fields = ModuleRegistry::get($this->run->module)['fields'];
        foreach ($this->exportColumns() as $field) {
            foreach (($fields[$field]['translated'] ?? false) ? ($this->run->locale === 'both' ? ['ar', 'en'] : [$this->run->locale]) : [''] as $locale) {
                $headings[] = trans('studio.field_'.$field, [], in_array($this->run->locale, ['ar', 'en']) ? $this->run->locale : 'en').($locale ? ' ('.$locale.')' : '');
            }
        }$headings[] = 'Created at';

        return $headings;
    }

    public function map(mixed $row): array
    {
        $result = [$row->id];
        $fields = ModuleRegistry::get($this->run->module)['fields'];
        foreach ($this->exportColumns() as $field) {
            foreach (($fields[$field]['translated'] ?? false) ? ($this->run->locale === 'both' ? ['ar', 'en'] : [$this->run->locale]) : [''] as $locale) {
                $value = $locale ? $row->text($field, $locale) : $row->getAttribute($field);
                $result[] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : strip_tags((string) $value);
            }
        }$result[] = $row->created_at?->toIso8601String();

        return $result;
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

        return true;
    }
}
