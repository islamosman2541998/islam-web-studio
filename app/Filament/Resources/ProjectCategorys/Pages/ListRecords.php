<?php

namespace App\Filament\Resources\ProjectCategorys\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\ProjectCategorys\ProjectCategoryResource;

class ListRecords extends ContentList
{
    protected static string $resource = ProjectCategoryResource::class;
}
