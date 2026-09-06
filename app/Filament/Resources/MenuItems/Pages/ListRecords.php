<?php

namespace App\Filament\Resources\MenuItems\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\MenuItems\MenuItemResource;

class ListRecords extends ContentList
{
    protected static string $resource = MenuItemResource::class;
}
