<?php

namespace App\Filament\Resources\PostCategorys\Pages;

use App\Filament\Pages\ContentEdit;
use App\Filament\Resources\PostCategorys\PostCategoryResource;

class EditRecord extends ContentEdit
{
    protected static string $resource = PostCategoryResource::class;
}
