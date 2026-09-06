@props(['label', 'href' => null])
@php($eyebrowText = \App\Support\Studio::translated('general.site_name', \App\Support\Studio::text('studio_name')).' / '.$label)
@if($href)
    <a {{ $attributes->class('eyebrow') }} href="{{ $href }}" wire:navigate>{{ $eyebrowText }}</a>
@else
    <span {{ $attributes->class('eyebrow') }}>{{ $eyebrowText }}</span>
@endif
