@extends('layouts.site')

@section('content')
    @php
        $galleryItems = $record->gallery
            ->filter(fn ($entry) => $entry->media && $entry->media_id !== $record->main_media_id)
            ->values();
        $galleryCount = ($record->mainMedia ? 1 : 0) + $galleryItems->count();
    @endphp

    <section class="page-intro shell project-intro">
        <x-page-eyebrow
            :label="\App\Support\Studio::text('route_projects.index').' / '.$record->projectCategory?->titleText()"
            :href="route('projects.index', ['locale' => app()->getLocale()])"
        />
        <h1>{{ $record->titleText() }}</h1>
    </section>

    @if($galleryCount)
        <section class="shell project-showcase">
            <div class="swiper project-gallery project-gallery--hero" data-studio-swiper data-swiper-kind="gallery" data-drag="1">
                <div class="swiper-wrapper">
                    @if($record->mainMedia)
                        <figure class="swiper-slide project-gallery-main">
                            <div @class(['desktop-project-media' => $record->mainMediaMobile])>
                                <x-media
                                    :asset="$record->mainMedia"
                                    :poster="$record->mainMediaPoster"
                                    :priority="true"
                                    sizes="90vw"
                                />
                            </div>
                            @if($record->mainMediaMobile)
                                <div class="mobile-project-media">
                                    <x-media
                                        :asset="$record->mainMediaMobile"
                                        :poster="$record->mainMediaPoster"
                                        :priority="true"
                                        sizes="100vw"
                                    />
                                </div>
                            @endif
                            @if($record->mainMedia->kind === 'image')
                                <button
                                    type="button"
                                    class="preview-button"
                                    data-gallery="{{ json_encode([['type' => 'image', 'src' => $record->mainMedia->imageUrl(1920), 'alt' => $record->titleText()]], JSON_UNESCAPED_UNICODE) }}"
                                    aria-label="{{ \App\Support\Studio::text('preview') }}"
                                >⤢</button>
                            @endif
                        </figure>
                    @endif

                    @foreach($galleryItems as $entry)
                        <figure class="swiper-slide">
                            <x-media :asset="$entry->media" sizes="90vw" />
                            @if($entry->text('caption'))
                                <figcaption>{{ $entry->text('caption') }}</figcaption>
                            @endif
                            @if($entry->media?->kind === 'image')
                                <button
                                    type="button"
                                    class="preview-button"
                                    data-gallery="{{ json_encode([['type' => 'image', 'src' => $entry->media->imageUrl(1920), 'alt' => $entry->text('caption')]], JSON_UNESCAPED_UNICODE) }}"
                                    aria-label="{{ \App\Support\Studio::text('preview') }}"
                                >⤢</button>
                            @endif
                        </figure>
                    @endforeach
                </div>

                @if($galleryCount > 1)
                    <div class="gallery-thumbnails">
                        @if($record->mainMedia)
                            <button type="button" data-slide-to="0" aria-label="{{ \App\Support\Studio::text('preview') }} 1">
                                @if($record->mainMedia->kind === 'image')
                                    <x-media :asset="$record->mainMedia" sizes="100px" />
                                @else
                                    <span>▶ 1</span>
                                @endif
                            </button>
                        @endif
                        @foreach($galleryItems as $entry)
                            @php($slideIndex = $loop->index + ($record->mainMedia ? 1 : 0))
                            <button type="button" data-slide-to="{{ $slideIndex }}" aria-label="{{ \App\Support\Studio::text('preview') }} {{ $slideIndex + 1 }}">
                                @if($entry->media?->kind === 'image')
                                    <x-media :asset="$entry->media" sizes="100px" />
                                @else
                                    <span>▶ {{ $slideIndex + 1 }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <div class="slider-controls">
                        <button type="button" class="swiper-prev" aria-label="{{ \App\Support\Studio::text('previous') }}">←</button>
                        <button type="button" class="swiper-next" aria-label="{{ \App\Support\Studio::text('next') }}">→</button>
                    </div>
                @endif
            </div>

            @if($record->live_url)
                <div class="project-showcase-link">
                    <a class="button" href="{{ \App\Support\Studio::safeUrl($record->live_url) }}" target="_blank" rel="noopener noreferrer">
                        @t('visit_project') <x-arrow-up-right />
                    </a>
                </div>
            @endif
        </section>
    @endif

    <section class="shell facts-bar">
        <div><span>@t('field_client')</span><strong>{{ $record->client }}</strong></div>
        <div><span>@t('field_duration')</span><strong>{{ $record->text('duration') }}</strong></div>
        <div><span>@t('services')</span><strong>{{ $record->services->filter(fn ($service) => $service->is_active && $service->status === 'published')->map(fn ($service) => $service->titleText())->join(' · ') }}</strong></div>
        <div><span>@t('field_tech_stack')</span><strong dir="ltr">{{ implode(' / ', $record->tech_stack ?? []) }}</strong></div>
        @foreach($record->metrics as $metric)
            <div class="fact-metric"><strong>{{ $metric->value }}</strong><span>{{ $metric->text('label') }}</span></div>
        @endforeach
    </section>

    <section class="section shell case-overview">
        <span class="eyebrow">01 / @t('project_overview')</span>
        <p>{{ $record->text('overview') }}</p>
    </section>

    <section class="section shell case-study">
        @foreach(['challenge', 'solution', 'result'] as $field)
            <article>
                <span class="item-number">0{{ $loop->iteration }}</span>
                <h2>@t($field)</h2>
                <div class="rich-content">{!! \App\Support\Studio::cleanHtml($record->text($field)) !!}</div>
            </article>
        @endforeach
    </section>

    @if($record->testimonial?->is_approved && $record->testimonial?->is_active && ! $record->testimonial?->is_demo)
        <section class="section shell project-review">
            <x-testimonial :review="$record->testimonial" />
        </section>
    @endif

    @if($related->count())
        <section class="section shell related-projects">
            <div class="section-heading"><h2>@t('related_projects')</h2></div>
            <div
                class="swiper related-projects-swiper"
                data-studio-swiper
                data-swiper-kind="related"
                data-drag="1"
                data-autoplay="1"
                data-delay="4200"
                data-loop="{{ $related->count() > 3 ? '1' : '0' }}"
            >
                <div class="swiper-wrapper">
                    @foreach($related as $project)
                        <div class="swiper-slide">
                            <x-project-card :project="$project" />
                        </div>
                    @endforeach
                </div>
                <div class="slider-controls related-projects-controls">
                    <button type="button" class="swiper-prev" aria-label="{{ \App\Support\Studio::text('previous') }}">←</button>
                    <button type="button" class="swiper-next" aria-label="{{ \App\Support\Studio::text('next') }}">→</button>
                </div>
            </div>
        </section>
    @endif
@endsection
