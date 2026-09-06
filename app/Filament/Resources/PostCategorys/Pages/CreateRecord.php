<?php

namespace App\Filament\Resources\PostCategorys\Pages;

use App\Filament\Pages\ContentCreate;
use App\Filament\Resources\PostCategorys\PostCategoryResource;

class CreateRecord extends ContentCreate
{
    protected static string $resource = PostCategoryResource::class;
}
