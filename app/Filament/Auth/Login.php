<?php

namespace App\Filament\Auth;

use App\Support\Studio;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

class Login extends \Filament\Auth\Pages\Login
{
    public function getHeading(): string|Htmlable|null
    {
        return Studio::translated('login.title', Studio::text('welcome'));
    }

    public function getSubheading(): string|Htmlable|null
    {
        return Studio::setting('login.show_description', true) ? Studio::translated('login.description') : null;
    }

    public function hasLogo(): bool
    {
        return (bool) Studio::setting('login.show_logo', true);
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()->visible((bool) Studio::setting('login.show_remember', true));
    }
}
