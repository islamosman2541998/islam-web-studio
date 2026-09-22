<?php

namespace App\Filament\Pages;

use App\Support\Studio;

class Dashboard extends \Filament\Pages\Dashboard
{
    public function getTitle(): string
    {
        return Studio::text('dashboard_title');
    }

    public static function getNavigationLabel(): string
    {
        return Studio::text('overview');
    }

    public function getSubheading(): ?string
    {
        return Studio::text('dashboard_subheading');
    }
}
