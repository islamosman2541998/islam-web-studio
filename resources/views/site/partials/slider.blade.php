@if($slider && $slider->slides->count())
<section class="hero" id="home-hero" aria-label="{{ $slider->name }}">
    <div
        class="swiper hero-swiper"
        data-studio-swiper
        data-swiper-kind="hero"
        data-autoplay="{{ $slider->autoplay ? 1 : 0 }}"
        data-delay="{{ $slider->autoplay_speed }}"
        data-loop="{{ $slider->loop ? 1 : 0 }}"
        data-drag="{{ $slider->draggable ? 1 : 0 }}"
    >
        <div class="swiper-wrapper">
            @foreach($slider->slides as $slide)
                @php
                    $slideTitle = $slide->text('title');
                    $slideDescription = $slide->text('description');
                    $slideButtonText = $slide->text('button_text');
                    $hasSlideContent = filled($slideTitle) || filled($slideDescription) || (filled($slideButtonText) && filled($slide->button_url));
                @endphp
                <div
                    class="swiper-slide hero-slide"
                    data-video-advance="{{ $slide->video_advance }}"
                    style="--slide-text:{{ \App\Support\Studio::color($slide->text_color, '#F7F4D5') }};--slide-button:{{ \App\Support\Studio::color($slide->button_color, '#D3968C') }};--slide-button-text:{{ \App\Support\Studio::color($slide->button_text_color, '#0A3323') }};--slide-button-hover:{{ \App\Support\Studio::color($slide->button_hover_color, '#F7F4D5') }};--hero-overlay-opacity:{{ min(80, max(0, $slide->overlay_opacity)) / 100 }}"
                >
                    <div class="hero-media" aria-hidden="true">
                        <div class="hero-media-desktop">
                            <x-media :asset="$slide->desktopMedia" :poster="$slide->desktopPoster" :priority="$loop->first" :controls="false" :defer-video="true" sizes="100vw" />
                        </div>
                        @if($slide->mobileMedia?->is_active && $slide->mobileMedia->visibility === 'public')
                            <div class="hero-media-mobile">
                                <x-media :asset="$slide->mobileMedia" :poster="$slide->mobilePoster" :priority="$loop->first" :controls="false" :defer-video="true" sizes="100vw" />
                            </div>
                        @endif
                    </div>
                    <div class="hero-scrim" aria-hidden="true"></div>
                    @if($hasSlideContent)
                        <div class="shell hero-layout">
                            <div class="hero-content">
                            @if(filled($slideTitle))
                                <h3 class="slide-title">{{ $slideTitle }}</h3>
                            @endif
                            @if(filled($slideDescription))
                                <p class="hero-description">{{ $slideDescription }}</p>
                            @endif
                            @if(filled($slideButtonText) && filled($slide->button_url))
                                <a class="button hero-button" href="{{ \App\Support\Studio::safeUrl(str_replace('{locale}', app()->getLocale(), $slide->button_url ?? '')) }}" wire:navigate>
                                    <span>{{ $slideButtonText }}</span><span aria-hidden="true">↗</span>
                                </a>
                            @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="shell hero-controls-wrap">
            <div class="slider-controls">
                @if($slider->dots)<div class="swiper-pagination"></div>@endif
                @if($slider->arrows)
                    <button type="button" class="swiper-prev" aria-label="{{ \App\Support\Studio::text('previous') }}">←</button>
                    <button type="button" class="swiper-next" aria-label="{{ \App\Support\Studio::text('next') }}">→</button>
                @endif
                @if($slider->autoplay)<button type="button" class="swiper-pause" aria-label="{{ \App\Support\Studio::text('pause') }}" aria-pressed="false">Ⅱ</button>@endif
            </div>
        </div>
    </div>
</section>
@else
<section class="hero hero-fallback" id="home-hero">
    <div class="hero-scrim" aria-hidden="true"></div>
    <div class="shell hero-layout">
        <div class="hero-content">
            <h3 class="slide-title">{{ \App\Support\Studio::translated('general.tagline') }}</h3>
            <a class="button hero-button" href="{{ route('contact', ['locale' => app()->getLocale()]) }}" wire:navigate>@t('request_quote') <span aria-hidden="true">↗</span></a>
        </div>
    </div>
</section>
@endif
