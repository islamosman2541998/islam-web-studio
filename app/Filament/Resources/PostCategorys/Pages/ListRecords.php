<?php

namespace App\Filament\Resources\PostCategorys\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\PostCategorys\PostCategoryResource;

class ListRecords extends ContentList
{
    protected static string $resource = PostCategoryResource::class;
}
