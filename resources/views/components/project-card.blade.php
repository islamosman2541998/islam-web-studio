@props(['project'])

@php
    $gallery = $project->gallery
        ->filter(fn ($entry) => $entry->media && $entry->media->visibility === 'public')
        ->map(fn ($entry) => [
            'type' => $entry->media->kind,
            'src' => $entry->media->kind === 'image'
                ? $entry->media->imageUrl(1440)
                : $entry->media->publicUrl(),
            'alt' => $entry->text('caption') ?: $entry->media->text('alt'),
        ])
        ->values()
        ->all();

    $isVideo = $project->main_media_type === 'video' && $project->mainMedia?->kind === 'video';
    $cover = $isVideo ? $project->mainMediaPoster : $project->mainMedia;
@endphp

<article {{ $attributes->class('project-card') }}>
    <div @class(['project-cover', 'project-cover--video' => $isVideo])>
        @if($isVideo)
            <x-studio-video
                :asset="$project->mainMedia"
                :poster="$cover"
                :title="$project->titleText()"
                variant="card"
                :defer="(bool) $cover"
            />

            <a
                class="project-cover-detail"
                href="{{ $project->publicUrl() }}"
                wire:navigate
                aria-label="{{ \App\Support\Studio::text('view_project') }} — {{ $project->titleText() }}"
            >
                <x-arrow-up-right />
            </a>
        @else
            <a href="{{ $project->publicUrl() }}" wire:navigate aria-label="{{ $project->titleText() }}">
                <x-media :asset="$cover" sizes="(max-width: 700px) 100vw, 50vw" />
            </a>
        @endif

        @if(count($gallery))
            <button
                type="button"
                class="preview-button"
                aria-label="{{ \App\Support\Studio::text('project_preview') }} — {{ $project->titleText() }}"
                data-gallery="{{ json_encode($gallery, JSON_UNESCAPED_UNICODE) }}"
            >⤢</button>
        @endif
    </div>

    <a class="project-description" href="{{ $project->publicUrl() }}" wire:navigate>
        <div>
            <span class="overline">{{ $project->projectCategory?->titleText() }}</span>
            <h3>{{ $project->titleText() }}</h3>
        </div>
        <span class="project-arrow"><x-arrow-up-right /></span>
    </a>

    @if($metric = $project->metrics->first())
        <div class="project-metric">
            <b>{{ $metric->value }}</b>
            <span>{{ $metric->text('label') }}</span>
        </div>
    @endif
</article>
