<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\Leads\LeadResource;

class ListRecords extends ContentList
{
    protected static string $resource = LeadResource::class;
}
