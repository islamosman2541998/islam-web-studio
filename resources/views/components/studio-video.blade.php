@props([
    'asset' => null,
    'poster' => null,
    'priority' => false,
    'variant' => 'default',
    'title' => null,
    'defer' => false,
])

@php
    $labels = app()->isLocale('ar')
        ? [
            'player' => 'مشغل فيديو',
            'video' => 'فيديو',
            'play' => 'تشغيل الفيديو',
            'pause' => 'إيقاف الفيديو مؤقتًا',
            'progress' => 'موضع تشغيل الفيديو',
            'mute' => 'كتم الصوت',
            'unmute' => 'تشغيل الصوت',
            'fullscreen' => 'عرض بملء الشاشة',
            'exit_fullscreen' => 'الخروج من ملء الشاشة',
            'loading' => 'جارٍ تحميل الفيديو',
            'error' => 'تعذر تشغيل الفيديو.',
        ]
        : [
            'player' => 'Video player',
            'video' => 'Video',
            'play' => 'Play video',
            'pause' => 'Pause video',
            'progress' => 'Video progress',
            'mute' => 'Mute sound',
            'unmute' => 'Unmute sound',
            'fullscreen' => 'Enter fullscreen',
            'exit_fullscreen' => 'Exit fullscreen',
            'loading' => 'Loading video',
            'error' => 'The video could not be played.',
        ];

    $resolvedVariant = in_array($variant, ['default', 'card', 'gallery'], true) ? $variant : 'default';
    $playerLabel = filled($title) ? $labels['player'].' — '.$title : $labels['player'];
@endphp

<div
    {{ $attributes->class(['studio-video', 'studio-video--'.$resolvedVariant]) }}
    data-studio-video
    data-play-label="{{ $labels['play'] }}"
    data-pause-label="{{ $labels['pause'] }}"
    data-mute-label="{{ $labels['mute'] }}"
    data-unmute-label="{{ $labels['unmute'] }}"
    data-fullscreen-label="{{ $labels['fullscreen'] }}"
    data-exit-fullscreen-label="{{ $labels['exit_fullscreen'] }}"
    tabindex="0"
    role="group"
    aria-label="{{ $playerLabel }}"
>
    @if($poster)
        <span
            class="studio-video__ambient"
            style="background-image:url('{{ $poster->imageUrl(1440) }}')"
            aria-hidden="true"
        ></span>
    @endif

    <x-media
        :asset="$asset"
        :poster="$poster"
        :priority="$priority"
        :controls="false"
        :muted="false"
        :defer-video="$defer"
        class="studio-video__media"
    />

    <div class="studio-video__shade" aria-hidden="true"></div>

    <div class="studio-video__topbar" aria-hidden="true">
        <span class="studio-video__kind"><i></i>{{ $labels['video'] }}</span>
        <output class="studio-video__duration" data-video-duration dir="ltr">0:00</output>
    </div>

    <span class="studio-video__loader" data-video-loading role="status" aria-label="{{ $labels['loading'] }}"></span>

    <button class="studio-video__center" type="button" data-video-toggle aria-label="{{ $labels['play'] }}">
        <span>
            <svg class="studio-video__play-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l10-6.5z" /></svg>
            <svg class="studio-video__pause-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z" /></svg>
        </span>
    </button>

    <div class="studio-video__controls swiper-no-swiping">
        <button class="studio-video__button studio-video__button--toggle" type="button" data-video-toggle aria-label="{{ $labels['play'] }}">
            <svg class="studio-video__play-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l10-6.5z" /></svg>
            <svg class="studio-video__pause-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z" /></svg>
        </button>

        <input
            class="studio-video__progress"
            type="range"
            min="0"
            max="100"
            step="0.1"
            value="0"
            data-video-progress
            aria-label="{{ $labels['progress'] }}"
        >

        <output class="studio-video__time" data-video-time dir="ltr">0:00 / 0:00</output>

        <button class="studio-video__button" type="button" data-video-mute aria-label="{{ $labels['mute'] }}">
            <svg class="studio-video__volume-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4zm12.2-.8a5.4 5.4 0 0 1 0 7.6l1.4 1.4a7.4 7.4 0 0 0 0-10.4l-1.4 1.4z" /></svg>
            <svg class="studio-video__muted-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4zm12.4 0L15 10.4l1.6 1.6-1.6 1.6 1.4 1.4 1.6-1.6 1.6 1.6 1.4-1.4-1.6-1.6 1.6-1.6L19.6 9 18 10.6 16.4 9z" /></svg>
        </button>

        <button class="studio-video__button" type="button" data-video-fullscreen aria-label="{{ $labels['fullscreen'] }}">
            <svg class="studio-video__expand-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9V5h4v2H7v2H5zm10-4h4v4h-2V7h-2V5zM7 15v2h2v2H5v-4h2zm10 2v-2h2v4h-4v-2h2z" /></svg>
            <svg class="studio-video__collapse-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5v4H5V7h2V5h2zm6 0h2v2h2v2h-4V5zM5 15h4v4H7v-2H5v-2zm10 0h4v2h-2v2h-2v-4z" /></svg>
        </button>
    </div>

    <p class="studio-video__error" data-video-error hidden>{{ $labels['error'] }}</p>
</div>
