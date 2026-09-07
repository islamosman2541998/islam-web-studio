@php
    $s = \App\Support\Studio::class;
    $smallTextWeight = (string) $s::setting('dashboard.small_text_weight', '600');
    $smallTextSize = (string) $s::setting('dashboard.small_text_size', '13');
    $smallTextWeight = in_array($smallTextWeight, ['500', '600', '700'], true) ? $smallTextWeight : '600';
    $smallTextSize = in_array($smallTextSize, ['12', '13', '14'], true) ? $smallTextSize : '13';
    $loginBackground = $s::color($s::setting('login.background'), '#0A3323');
    $loginOverlay = $s::rgba($loginBackground, $s::setting('login.background_overlay_opacity', 79), '#0A3323');
    $loginCard = $s::rgba($s::setting('login.card_background'), $s::setting('login.card_opacity', 100), '#FFFDF4');
    $loginFontSize = fn (string $key, int $fallback): int => max(9, min(48, (int) $s::setting('login.'.$key, $fallback)));
    $background = \App\Models\Asset::find($s::setting('login.background_image'));
@endphp

<style>
    :root {
        --studio-admin-primary: {{ $s::color($s::setting('dashboard.primary'), '#105666') }};
        --studio-admin-bg: {{ $s::color($s::setting('dashboard.light_background'), '#F7F4D5') }};
        --studio-admin-surface: {{ $s::color($s::setting('dashboard.light_surface'), '#FFFDF4') }};
        --studio-admin-text: {{ $s::color($s::setting('dashboard.light_text'), '#0A3323') }};
        --studio-admin-small-weight:{{ $smallTextWeight }};
        --studio-admin-small-size:{{ $smallTextSize }}px;
        --studio-login-bg: {{ $loginBackground }};
        --studio-login-overlay: {{ $loginOverlay }};
        --studio-login-card: {{ $loginCard }};
        --studio-login-title-color: {{ $s::color($s::setting('login.title_text_color'), '#18181B') }};
        --studio-login-title-size: {{ $loginFontSize('title_text_size', 24) }}px;
        --studio-login-description-color: {{ $s::color($s::setting('login.description_text_color'), '#71717A') }};
        --studio-login-description-size: {{ $loginFontSize('description_text_size', 14) }}px;
        --studio-login-label-color: {{ $s::color($s::setting('login.label_text_color'), '#18181B') }};
        --studio-login-label-size: {{ $loginFontSize('label_text_size', 14) }}px;
        --studio-login-input-color: {{ $s::color($s::setting('login.input_text_color'), '#18181B') }};
        --studio-login-input-size: {{ $loginFontSize('input_text_size', 14) }}px;
        --studio-login-button-text-color: {{ $s::color($s::setting('login.button_text_color'), '#FFFFFF') }};
        --studio-login-button-text-size: {{ $loginFontSize('button_text_size', 14) }}px;
        --studio-login-link-color: {{ $s::color($s::setting('login.link_text_color'), '#0A3323') }};
        --studio-login-link-size: {{ $loginFontSize('link_text_size', 13) }}px;
        --studio-login-footer-color: {{ $s::color($s::setting('login.footer_text_color'), '#6B7A74') }};
        --studio-login-footer-size: {{ $loginFontSize('footer_text_size', 11) }}px;
    }

    .dark {
        --studio-admin-bg: {{ $s::color($s::setting('dashboard.dark_background'), '#071F17') }};
        --studio-admin-surface: {{ $s::color($s::setting('dashboard.dark_surface'), '#0A3323') }};
        --studio-admin-text: {{ $s::color($s::setting('dashboard.dark_text'), '#F7F4D5') }};
    }
</style>

@if ($background)
    <style>
        .fi-simple-layout {
            background-color: var(--studio-login-bg) !important;
            background-image: linear-gradient(var(--studio-login-overlay), var(--studio-login-overlay)), url('{{ $background->imageUrl(1440) }}') !important;
            background-position: center !important;
            background-size: cover !important;
        }
    </style>
@endif
