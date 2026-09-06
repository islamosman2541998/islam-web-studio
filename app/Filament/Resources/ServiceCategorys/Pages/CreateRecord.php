<?php

namespace App\Filament\Resources\ServiceCategorys\Pages;

use App\Filament\Pages\ContentCreate;
use App\Filament\Resources\ServiceCategorys\ServiceCategoryResource;

class CreateRecord extends ContentCreate
{
    protected static string $resource = ServiceCategoryResource::class;
}
