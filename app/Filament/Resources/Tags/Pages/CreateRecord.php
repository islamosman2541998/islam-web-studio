<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Pages\ContentCreate;
use App\Filament\Resources\Tags\TagResource;

class CreateRecord extends ContentCreate
{
    protected static string $resource = TagResource::class;
}
