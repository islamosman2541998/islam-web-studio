@extends('layouts.site')
@section('content')
@include('site.partials.slider')
<section class="section shell services-section">
    <header class="services-heading" data-reveal>
        <div class="services-heading-title">
            <span class="services-kicker"><b>01</b><span>@t('what_we_do')</span></span>
            <h3>@t('services_title')</h3>
            <p>@t('services_intro')</p>
        </div>
        <a class="text-link" href="{{ route('services.index',['locale'=>app()->getLocale()]) }}" wire:navigate>@t('view_all') <span aria-hidden="true">↗</span></a>
    </header>
    <div class="services-grid">@foreach($services as $service)<x-service-card :service="$service" :number="$loop->iteration" />@endforeach</div>
</section>
<section class="section work-section">
    <div class="shell">
        <div class="section-heading">
            <span class="eyebrow">02 / @t('selected_work')</span>
            <div><h2>@t('projects_title')</h2></div>
            <a class="text-link" href="{{ route('projects.index',['locale'=>app()->getLocale()]) }}" wire:navigate>@t('view_all') ↗</a>
        </div>
        <div class="projects-grid">@foreach($projects as $project)<x-project-card :project="$project" />@endforeach</div>
    </div>
</section>
@php
    $introMediaEnabled = (bool) \App\Support\Studio::setting('home.intro_media_enabled', true);
    $introMediaFitSetting = (string) \App\Support\Studio::setting('home.intro_media_fit', 'cover');
    $introMediaFit = in_array($introMediaFitSetting, ['cover', 'contain'], true) ? $introMediaFitSetting : 'cover';
    $introLinkRaw = str_replace('{locale}', app()->getLocale(), (string) \App\Support\Studio::setting('home.intro_link_url', '/{locale}/about'));
    $introLink = \App\Support\Studio::safeUrl($introLinkRaw);
    $introLinkNewTab = (bool) \App\Support\Studio::setting('home.intro_link_new_tab', false);
    $introLinkText = \App\Support\Studio::translated('home.intro_link_text', \App\Support\Studio::text('route_about'));
    $introFounderLabel = \App\Support\Studio::translated('home.intro_founder_label', \App\Support\Studio::text('founder'));
    $introSignature = \App\Support\Studio::translated('home.intro_signature', 'Islam.');
@endphp
@if(\App\Support\Studio::setting('home.intro_enabled', true))
<section id="home-intro" @class(['section', 'shell', 'about-intro', 'about-intro--without-media' => ! $introMediaEnabled])>
    @if($introMediaEnabled)
        <div
            @class(['intro-mark', 'intro-mark--asset' => $introMedia, 'intro-mark--no-frame' => ! \App\Support\Studio::setting('home.intro_frame_enabled', true)])
            style="--intro-media-background:{{ \App\Support\Studio::color(\App\Support\Studio::setting('home.intro_media_background'), '#839958') }};--intro-media-caption-color:{{ \App\Support\Studio::color(\App\Support\Studio::setting('home.intro_media_caption_color'), '#0A3323') }};--intro-media-fit:{{ $introMediaFit }}"
        >
            @if($introMedia)
                <x-media :asset="$introMedia" class="intro-mark-media" sizes="(max-width: 767px) 100vw, 50vw" />
            @else
                <img class="intro-default-mark" src="{{ asset('brand/mark.svg') }}" width="210" height="240" alt="">
            @endif
            @if(filled(\App\Support\Studio::translated('home.intro_media_caption', "WE CONNECT\nTHE DOTS.")))
                <span class="preserve-lines">{{ str_replace('\\n', "\n", \App\Support\Studio::translated('home.intro_media_caption', "WE CONNECT\nTHE DOTS.")) }}</span>
            @endif
        </div>
    @endif
    <div class="intro-copy">
        @if(filled(\App\Support\Studio::translated('home.intro_label')))
            <span class="eyebrow">{{ \App\Support\Studio::translated('home.intro_label') }}</span>
        @endif
        @if(filled(\App\Support\Studio::translated('home.intro_title')))
            <h2 class="preserve-lines">{{ str_replace('\\n', "\n", \App\Support\Studio::translated('home.intro_title')) }}</h2>
        @endif
        @if(filled(\App\Support\Studio::translated('home.intro_text')))
            <p class="preserve-lines">{{ str_replace('\\n', "\n", \App\Support\Studio::translated('home.intro_text')) }}</p>
        @endif
        @if(\App\Support\Studio::setting('home.intro_link_enabled', true) && filled($introLinkText) && $introLink !== '#')
            <a class="text-link" href="{{ $introLink }}" @if(! $introLinkNewTab && str_starts_with($introLinkRaw, '/')) wire:navigate @endif @if($introLinkNewTab) target="_blank" rel="noopener noreferrer" @endif>{{ $introLinkText }} ↗</a>
        @endif
        @if(filled($introFounderLabel) || filled($introSignature))
            <div class="intro-signature">
                @if(filled($introFounderLabel))<span>{{ $introFounderLabel }}</span>@endif
                @if(filled($introSignature))<span class="signature" dir="auto">{{ $introSignature }}</span>@endif
            </div>
        @endif
    </div>
</section>
@endif
<section class="section process-section"><div class="shell process-grid"><div><span class="eyebrow">03 / @t('our_process')</span><h2>@t('methodology_title')</h2><p>@t('methodology_intro')</p></div>@include('site.partials.process')</div></section>
<section class="section shell"><div class="section-heading"><span class="eyebrow">04 / @t('client_words')</span><div><h2>@t('testimonials_title')</h2></div></div><div class="swiper reviews-swiper" data-studio-swiper data-swiper-kind="reviews" data-drag="1"><div class="swiper-wrapper">@foreach($testimonials as $review)<div class="swiper-slide"><x-testimonial :review="$review" /></div>@endforeach</div><div class="slider-controls"><button class="swiper-prev" type="button" aria-label="{{ \App\Support\Studio::text('previous') }}">←</button><button class="swiper-next" type="button" aria-label="{{ \App\Support\Studio::text('next') }}">→</button></div></div></section>
<section class="section insights-section"><div class="shell"><div class="section-heading"><span class="eyebrow">05 / @t('insights')</span><div><h2>@t('posts_title')</h2></div><a class="text-link" href="{{ route('posts.index',['locale'=>app()->getLocale()]) }}" wire:navigate>@t('view_all') ↗</a></div><div class="posts-grid">@foreach($posts as $post)<x-post-card :post="$post" />@endforeach</div></div></section>
@endsection
