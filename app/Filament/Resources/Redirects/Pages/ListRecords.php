<?php

namespace App\Filament\Resources\Redirects\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\Redirects\RedirectResource;

class ListRecords extends ContentList
{
    protected static string $resource = RedirectResource::class;
}
