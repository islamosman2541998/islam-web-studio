<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Pages\ContentCreate;
use App\Filament\Resources\Leads\LeadResource;

class CreateRecord extends ContentCreate
{
    protected static string $resource = LeadResource::class;
}
