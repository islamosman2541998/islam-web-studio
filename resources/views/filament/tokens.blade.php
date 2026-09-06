@php
    $s = \App\Support\Studio::class;
    $smallTextWeight = (string) $s::setting('dashboard.small_text_weight', '600');
    $smallTextSize = (string) $s::setting('dashboard.small_text_size', '13');
    $smallTextWeight = in_array($smallTextWeight, ['500', '600', '700'], true) ? $smallTextWeight : '600';
    $smallTextSize = in_array($smallTextSize, ['12', '13', '14'], true) ? $smallTextSize : '13';
@endphp
<style>:root{--studio-admin-primary:{{ $s::color($s::setting('dashboard.primary'),'#105666') }};--studio-admin-bg:{{ $s::color($s::setting('dashboard.light_background'),'#F7F4D5') }};--studio-admin-surface:{{ $s::color($s::setting('dashboard.light_surface'),'#FFFDF4') }};--studio-admin-text:{{ $s::color($s::setting('dashboard.light_text'),'#0A3323') }};--studio-admin-small-weight:{{ $smallTextWeight }};--studio-admin-small-size:{{ $smallTextSize }}px;--studio-login-bg:{{ $s::color($s::setting('login.background'),'#0A3323') }};}.dark{--studio-admin-bg:{{ $s::color($s::setting('dashboard.dark_background'),'#071F17') }};--studio-admin-surface:{{ $s::color($s::setting('dashboard.dark_surface'),'#0A3323') }};--studio-admin-text:{{ $s::color($s::setting('dashboard.dark_text'),'#F7F4D5') }};}</style>
@if($background=\App\Models\Asset::find($s::setting('login.background_image')))<style>.fi-simple-layout{background-image:linear-gradient(#0a3323c9,#0a3323c9),url('{{ $background->imageUrl(1440) }}')!important;background-size:cover!important;}</style>@endif
