@php
    $logoOnLight = \App\Models\Asset::publicImage(\App\Support\Studio::setting('general.logo_light'));
    $logoOnDark = \App\Models\Asset::publicImage(\App\Support\Studio::setting('general.logo_dark'));
    $fallbackLogo = $logoOnLight ?: $logoOnDark;
@endphp
<a href="{{ route('home',['locale'=>app()->getLocale()]) }}" class="brand" wire:navigate aria-label="Islam Web Studio — {{ \App\Support\Studio::text('home') }}">
@if($fallbackLogo)
<span class="brand-custom-frame">
    <img class="brand-custom brand-custom-on-light" src="{{ ($logoOnLight ?: $fallbackLogo)->imageUrl(320) }}" width="156" height="46" alt="Islam Web Studio">
    <img class="brand-custom brand-custom-on-dark" src="{{ ($logoOnDark ?: $fallbackLogo)->imageUrl(320) }}" width="156" height="46" alt="" aria-hidden="true">
</span>
@else
<img class="brand-mark brand-mark-dark" src="{{ asset('brand/mark.svg') }}" width="42" height="48" alt=""><img class="brand-mark brand-mark-light" src="{{ asset('brand/mark-light.svg') }}" width="42" height="48" alt=""><span dir="ltr">Islam Web Studio<small>DIGITAL SOLUTIONS</small></span>
@endif
</a>
