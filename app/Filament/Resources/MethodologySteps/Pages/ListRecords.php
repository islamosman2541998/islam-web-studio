<?php

namespace App\Filament\Resources\MethodologySteps\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\MethodologySteps\MethodologyStepResource;

class ListRecords extends ContentList
{
    protected static string $resource = MethodologyStepResource::class;
}
