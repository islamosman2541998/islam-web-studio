<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class AccessExport extends StringValueBinder implements FromCollection, WithCustomValueBinder, WithHeadings
{
    public function __construct(public Collection $rows, public string $module) {}

    public function collection(): Enumerable
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->module === 'users' ? ['ID', 'Name', 'Email', 'Roles', 'Active', 'Created at'] : ['ID', 'Role', 'Permissions', 'Created at'];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

        return true;
    }
}
