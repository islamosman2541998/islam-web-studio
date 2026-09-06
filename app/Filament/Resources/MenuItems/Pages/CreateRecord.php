<?php

namespace App\Filament\Resources\MenuItems\Pages;

use App\Filament\Pages\ContentCreate;
use App\Filament\Resources\MenuItems\MenuItemResource;

class CreateRecord extends ContentCreate
{
    protected static string $resource = MenuItemResource::class;
}
