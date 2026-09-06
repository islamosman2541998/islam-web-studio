<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\LeadPipeline;
use App\Filament\Pages\MenuBuilder;
use App\Filament\Pages\StudioSettings;
use App\Filament\Widgets\StudioStats;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Support\FontRegistry;
use App\Support\Studio;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->default()->id('admin')->path('admin')->login(Login::class)
            ->brandName(fn () => Studio::translated('general.site_name', 'Islam Web Studio'))
            ->brandLogo(fn () => view('filament.brand'))->brandLogoHeight('3rem')
            ->colors(fn () => ['primary' => Studio::palette(Studio::color(Studio::setting('dashboard.primary'), '#105666'))])
            ->font(fn (): string => app()->getLocale() === 'ar'
                ? FontRegistry::arabic(Studio::setting('design.arabic_font'))
                : FontRegistry::english(Studio::setting('design.english_font')), url: asset('brand/fonts.css'), provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')->darkMode()->sidebarCollapsibleOnDesktop(fn () => Studio::setting('dashboard.compact_sidebar', true))->spa()
            ->databaseNotifications()->databaseNotificationsPolling('10s')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([Dashboard::class, StudioSettings::class, MenuBuilder::class, LeadPipeline::class])
            ->widgets([StudioStats::class])
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.tokens'))
            ->renderHook(PanelsRenderHook::BODY_END, fn () => view('filament.scripts'))
            ->renderHook(PanelsRenderHook::TOPBAR_END, fn () => view('filament.locale'))
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn () => view('filament.login-footer'))
            ->middleware([EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class, AuthenticateSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class, SubstituteBindings::class, SetLocale::class, SecurityHeaders::class, DisableBladeIconComponents::class, DispatchServingFilamentEvent::class])
            ->authMiddleware([Authenticate::class]);
    }
}
