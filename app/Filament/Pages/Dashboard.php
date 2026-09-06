<?php

namespace App\Filament\Pages;

use App\Support\Studio;

class Dashboard extends \Filament\Pages\Dashboard
{
    public function getTitle(): string
    {
        return Studio::text('welcome');
    }

    public static function getNavigationLabel(): string
    {
        return Studio::text('overview');
    }

    public function getSubheading(): ?string
    {
        return config('studio.demo') ? Studio::text('demo_notice') : null;
    }
}
