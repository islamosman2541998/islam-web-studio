<?php

namespace App\Filament\Resources\Redirects\Pages;

use App\Filament\Pages\ContentCreate;
use App\Filament\Resources\Redirects\RedirectResource;

class CreateRecord extends ContentCreate
{
    protected static string $resource = RedirectResource::class;
}
