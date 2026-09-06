<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\Posts\PostResource;

class ListRecords extends ContentList
{
    protected static string $resource = PostResource::class;
}
