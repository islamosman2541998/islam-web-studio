<?php

namespace App\Filament\Resources\ServiceCategorys\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\ServiceCategorys\ServiceCategoryResource;

class ListRecords extends ContentList
{
    protected static string $resource = ServiceCategoryResource::class;
}
