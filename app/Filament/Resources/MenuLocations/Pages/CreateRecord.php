<?php

namespace App\Filament\Resources\MenuLocations\Pages;

use App\Filament\Pages\ContentCreate;
use App\Filament\Resources\MenuLocations\MenuLocationResource;

class CreateRecord extends ContentCreate
{
    protected static string $resource = MenuLocationResource::class;
}
