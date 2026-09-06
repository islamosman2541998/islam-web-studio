<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Pages\ContentEdit;
use App\Filament\Resources\Testimonials\TestimonialResource;

class EditRecord extends ContentEdit
{
    protected static string $resource = TestimonialResource::class;
}
