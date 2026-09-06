<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Pages\ContentCreate;
use App\Filament\Resources\Posts\PostResource;

class CreateRecord extends ContentCreate
{
    protected static string $resource = PostResource::class;
}
