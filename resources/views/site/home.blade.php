@extends('layouts.site')
@section('content')
@include('site.partials.slider')
@php
    $homeSections = [
        'services' => [
            'label' => \App\Support\Studio::text('what_we_do'),
            'title' => \App\Support\Studio::text('services_title'),
            'description' => \App\Support\Studio::text('services_intro'),
        ],
        'projects' => [
            'label' => \App\Support\Studio::text('selected_work'),
            'title' => \App\Support\Studio::text('projects_title'),
            'description' => \App\Support\Studio::text('projects_intro'),
        ],
        'process' => [
            'label' => \App\Support\Studio::text('our_process'),
            'title' => \App\Support\Studio::text('methodology_title'),
            'description' => \App\Support\Studio::text('methodology_intro'),
        ],
        'testimonials' => [
            'label' => \App\Support\Studio::text('client_words'),
            'title' => \App\Support\Studio::text('testimonials_title'),
            'description' => \App\Support\Studio::text('testimonials_intro'),
        ],
        'posts' => [
            'label' => \App\Support\Studio::text('insights'),
            'title' => \App\Support\Studio::text('posts_title'),
            'description' => \App\Support\Studio::text('posts_intro'),
        ],
    ];
@endphp
<section class="section shell services-section">
    <header class="services-heading" data-reveal>
        <div class="services-heading-title">
            <span class="services-kicker"><b>01</b>@if(filled($homeSections['services']['label']))<span>{{ $homeSections['services']['label'] }}</span>@endif</span>
            @if(filled($homeSections['services']['title']))<h3>{{ $homeSections['services']['title'] }}</h3>@endif
            @if(filled($homeSections['services']['description']))<p>{{ $homeSections['services']['description'] }}</p>@endif
        </div>
        <a class="text-link" href="{{ route('services.index',['locale'=>app()->getLocale()]) }}" wire:navigate>@t('view_all') <x-arrow-up-right /></a>
    </header>
    <div class="services-grid">@foreach($services as $service)<x-service-card :service="$service" :number="$loop->iteration" />@endforeach</div>
</section>
<section class="section work-section">
    <div class="shell">
        <div class="section-heading">
            <span class="eyebrow">02{{ filled($homeSections['projects']['label']) ? ' / '.$homeSections['projects']['label'] : '' }}</span>
            <div class="section-heading-copy">
                @if(filled($homeSections['projects']['title']))<h2>{{ $homeSections['projects']['title'] }}</h2>@endif
                @if(filled($homeSections['projects']['description']))<p>{{ $homeSections['projects']['description'] }}</p>@endif
            </div>
            <a class="text-link" href="{{ route('projects.index',['locale'=>app()->getLocale()]) }}" wire:navigate>@t('view_all') <x-arrow-up-right /></a>
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
                    <a class="text-link" href="{{ $introLink }}" @if(! $introLinkNewTab && str_starts_with($introLinkRaw, '/')) wire:navigate @endif @if($introLinkNewTab) target="_blank" rel="noopener noreferrer" @endif>{{ $introLinkText }} <x-arrow-up-right /></a>
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
<section class="section process-section"><div class="shell process-grid"><div><span class="eyebrow">03{{ filled($homeSections['process']['label']) ? ' / '.$homeSections['process']['label'] : '' }}</span>@if(filled($homeSections['process']['title']))<h2>{{ $homeSections['process']['title'] }}</h2>@endif @if(filled($homeSections['process']['description']))<p>{{ $homeSections['process']['description'] }}</p>@endif</div>@include('site.partials.process')</div></section>
<section class="section shell"><div class="section-heading"><span class="eyebrow">04{{ filled($homeSections['testimonials']['label']) ? ' / '.$homeSections['testimonials']['label'] : '' }}</span><div class="section-heading-copy">@if(filled($homeSections['testimonials']['title']))<h2>{{ $homeSections['testimonials']['title'] }}</h2>@endif @if(filled($homeSections['testimonials']['description']))<p>{{ $homeSections['testimonials']['description'] }}</p>@endif</div></div><div class="swiper reviews-swiper" data-studio-swiper data-swiper-kind="reviews" data-drag="1"><div class="swiper-wrapper">@foreach($testimonials as $review)<div class="swiper-slide"><x-testimonial :review="$review" /></div>@endforeach</div><div class="slider-controls"><button class="swiper-prev" type="button" aria-label="{{ \App\Support\Studio::text('previous') }}">←</button><button class="swiper-next" type="button" aria-label="{{ \App\Support\Studio::text('next') }}">→</button></div></div></section>
<section class="section insights-section"><div class="shell"><div class="section-heading"><span class="eyebrow">05{{ filled($homeSections['posts']['label']) ? ' / '.$homeSections['posts']['label'] : '' }}</span><div class="section-heading-copy">@if(filled($homeSections['posts']['title']))<h2>{{ $homeSections['posts']['title'] }}</h2>@endif @if(filled($homeSections['posts']['description']))<p>{{ $homeSections['posts']['description'] }}</p>@endif</div><a class="text-link" href="{{ route('posts.index',['locale'=>app()->getLocale()]) }}" wire:navigate>@t('view_all') <x-arrow-up-right /></a></div><div class="posts-grid">@foreach($posts as $post)<x-post-card :post="$post" />@endforeach</div></div></section>
@endsection
