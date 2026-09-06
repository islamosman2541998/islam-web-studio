<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Pages\ContentCreate;
use App\Filament\Resources\Testimonials\TestimonialResource;

class CreateRecord extends ContentCreate
{
    protected static string $resource = TestimonialResource::class;
}
