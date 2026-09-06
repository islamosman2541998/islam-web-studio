<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\Testimonials\TestimonialResource;

class ListRecords extends ContentList
{
    protected static string $resource = TestimonialResource::class;
}
