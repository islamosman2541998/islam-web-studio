<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Pages\ContentEdit;
use App\Filament\Resources\Posts\PostResource;

class EditRecord extends ContentEdit
{
    protected static string $resource = PostResource::class;
}
