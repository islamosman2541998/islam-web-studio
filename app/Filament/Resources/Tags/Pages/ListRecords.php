<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\Tags\TagResource;

class ListRecords extends ContentList
{
    protected static string $resource = TagResource::class;
}
