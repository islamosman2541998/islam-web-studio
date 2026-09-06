<?php

namespace App\Filament\Resources\ProjectCategorys\Pages;

use App\Filament\Pages\ContentCreate;
use App\Filament\Resources\ProjectCategorys\ProjectCategoryResource;

class CreateRecord extends ContentCreate
{
    protected static string $resource = ProjectCategoryResource::class;
}
