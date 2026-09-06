<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Pages\ContentEdit;
use App\Filament\Resources\Leads\LeadResource;

class EditRecord extends ContentEdit
{
    protected static string $resource = LeadResource::class;
}
