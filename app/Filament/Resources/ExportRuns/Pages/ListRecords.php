<?php

namespace App\Filament\Resources\ExportRuns\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\ExportRuns\ExportRunResource;

class ListRecords extends ContentList
{
    protected static string $resource = ExportRunResource::class;
}
