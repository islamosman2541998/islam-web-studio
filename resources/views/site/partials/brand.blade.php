@php($logo=\App\Models\Asset::find(\App\Support\Studio::setting('general.logo_light')))
<a href="{{ route('home',['locale'=>app()->getLocale()]) }}" class="brand" wire:navigate aria-label="Islam Web Studio — {{ \App\Support\Studio::text('home') }}">
@if($logo)
<span class="brand-custom-frame"><img class="brand-custom" src="{{ $logo->imageUrl(320) }}" width="170" height="54" alt="Islam Web Studio"></span>
@else
<img class="brand-mark brand-mark-dark" src="{{ asset('brand/mark.svg') }}" width="42" height="48" alt=""><img class="brand-mark brand-mark-light" src="{{ asset('brand/mark-light.svg') }}" width="42" height="48" alt=""><span dir="ltr">Islam Web Studio<small>DIGITAL SOLUTIONS</small></span>
@endif
</a>