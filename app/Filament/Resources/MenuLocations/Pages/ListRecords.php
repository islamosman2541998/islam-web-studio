<?php

namespace App\Filament\Resources\MenuLocations\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\MenuLocations\MenuLocationResource;

class ListRecords extends ContentList
{
    protected static string $resource = MenuLocationResource::class;
}
