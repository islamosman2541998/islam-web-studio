@extends('layouts.site')

@section('content')
<article class="service-detail-page">
    <section class="page-intro service-page-intro service-shell shell">
        <x-page-eyebrow
            :label="\App\Support\Studio::text('route_services.index').' / '.$record->serviceCategory?->titleText()"
            :href="route('services.index', ['locale' => app()->getLocale()])"
        />
        <h1>{{ $record->titleText() }}</h1>
        <p>{{ $record->text('short_description') }}</p>
        <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="button" wire:navigate>
            @t('request_quote') <x-arrow-up-right />
        </a>
    </section>

    <div @class(['service-overview', 'service-shell', 'shell', 'service-overview--without-media' => ! $record->imageMedia])>
        @if($record->imageMedia)
            <div class="detail-hero service-detail-hero">
                <x-media :asset="$record->imageMedia" :priority="true" sizes="(max-width: 767px) 90vw, 540px" />
            </div>
        @endif

        <section class="service-detail-content">
            <div class="rich-content">{!! \App\Support\Studio::cleanHtml($record->text('long_description')) !!}</div>
            <aside>
                <h2>@t('included')</h2>
                <ul class="feature-list">
                    @foreach($record->features as $feature)
                        <li><span>✓</span>{{ $feature->text('text') }}</li>
                    @endforeach
                </ul>
            </aside>
        </section>
    </div>

    @if($record->pricing_type === 'packages' && $record->packages->isNotEmpty())
        <section class="section service-shell shell service-packages">
            <div class="section-heading"><h2>@t('packages')</h2></div>
            <div class="packages-grid">
                @foreach($record->packages as $package)
                    <article class="package">
                        <h3>{{ $package->titleText() }}</h3>
                        <div class="package-price">{{ number_format($package->price) }} <small>{{ $package->currency }}</small></div>
                        <ul>
                            @foreach(explode("\n", $package->text('features')) as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <a class="button" href="{{ route('contact', ['locale' => app()->getLocale()]) }}" wire:navigate>
                            @t('request_quote') <x-arrow-up-right />
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    @else
        <section class="service-shell shell service-request">
            <h2>@t('pricing_request')</h2>
        </section>
    @endif

    @if($projects->count())
        <section class="section shell service-related">
            <div class="section-heading"><h2>@t('service_related_title')</h2></div>
            <div class="projects-grid">
                @foreach($projects as $project)
                    <x-project-card :project="$project" />
                @endforeach
            </div>
        </section>
    @endif
</article>
@endsection
