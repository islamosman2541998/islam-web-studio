@extends('layouts.site')

@section('content')
    <section class="page-intro dynamic-page-intro shell">
        <x-page-eyebrow :label="\App\Support\Studio::text(($isAbout ?? false) ? 'about' : 'pages')" />
        <h1>{{ $record->titleText() }}</h1>
        <p>{{ $record->text('excerpt') }}</p>
    </section>

    <section @class([
        'section',
        'shell',
        'dynamic-page-story',
        'dynamic-page-story--without-media' => ! $record->heroMedia,
    ])>
        <div class="rich-content" data-reveal="up">
            {!! \App\Support\Studio::cleanHtml($record->text('content')) !!}
        </div>

        @if ($record->heroMedia)
            <div class="dynamic-page-story-media" data-reveal="side" style="--reveal-delay: 100ms">
                @if ($record->heroMedia->kind === 'video')
                    <x-studio-video
                        :asset="$record->heroMedia"
                        :poster="$record->heroPoster"
                        :priority="true"
                    />
                @else
                    <x-media
                        :asset="$record->heroMedia"
                        :poster="$record->heroPoster"
                        :priority="true"
                        sizes="(max-width: 767px) 100vw, 50vw"
                    />
                @endif
            </div>
        @endif
    </section>

    @if ($record->gallery->count())
        <section class="section shell dynamic-page-gallery-section">
            <div class="page-gallery">
                @foreach ($record->gallery as $entry)
                    @if ($entry->kind !== 'file')
                        @php($revealDirection = ['side', 'up', 'down'][$loop->index % 3])
                        <figure
                            data-reveal="{{ $revealDirection }}"
                            style="--reveal-delay: {{ ($loop->index % 3) * 90 }}ms"
                        >
                            <div class="page-gallery-media">
                                @if ($entry->media?->kind === 'video')
                                    <x-studio-video :asset="$entry->media" />
                                @else
                                    <x-media :asset="$entry->media" sizes="(max-width: 700px) 100vw, 50vw" />
                                @endif
                            </div>
                            @if (filled($entry->text('caption')))
                                <figcaption>{{ $entry->text('caption') }}</figcaption>
                            @endif
                        </figure>
                    @endif
                @endforeach
            </div>

            <div class="file-downloads">
                @foreach ($record->gallery as $entry)
                    @if ($entry->kind === 'file' && $entry->media?->visibility === 'public')
                        <a href="{{ $entry->media->publicUrl() }}" download data-reveal="up">
                            @t('download') ↓ {{ $entry->text('caption') ?: $entry->media->titleText() }}
                        </a>
                    @endif
                @endforeach
            </div>
        </section>
    @endif
@endsection
