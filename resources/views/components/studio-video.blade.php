@props(['asset' => null, 'poster' => null, 'priority' => false])

@php
    $labels = app()->isLocale('ar')
        ? [
            'play' => 'تشغيل الفيديو',
            'pause' => 'إيقاف الفيديو مؤقتًا',
            'progress' => 'موضع تشغيل الفيديو',
            'mute' => 'كتم الصوت',
            'unmute' => 'تشغيل الصوت',
            'fullscreen' => 'عرض بملء الشاشة',
            'exit_fullscreen' => 'الخروج من ملء الشاشة',
        ]
        : [
            'play' => 'Play video',
            'pause' => 'Pause video',
            'progress' => 'Video progress',
            'mute' => 'Mute sound',
            'unmute' => 'Unmute sound',
            'fullscreen' => 'Enter fullscreen',
            'exit_fullscreen' => 'Exit fullscreen',
        ];
@endphp

<div
    class="studio-video is-muted"
    data-studio-video
    data-play-label="{{ $labels['play'] }}"
    data-pause-label="{{ $labels['pause'] }}"
    data-mute-label="{{ $labels['mute'] }}"
    data-unmute-label="{{ $labels['unmute'] }}"
    data-fullscreen-label="{{ $labels['fullscreen'] }}"
    data-exit-fullscreen-label="{{ $labels['exit_fullscreen'] }}"
    tabindex="0"
>
    <x-media
        :asset="$asset"
        :poster="$poster"
        :priority="$priority"
        :controls="false"
        class="studio-video__media"
    />

    <button class="studio-video__center" type="button" data-video-toggle aria-label="{{ $labels['play'] }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l10-6.5z" /></svg>
    </button>

    <div class="studio-video__controls">
        <button class="studio-video__button" type="button" data-video-toggle aria-label="{{ $labels['play'] }}">
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

        <button class="studio-video__button" type="button" data-video-mute aria-label="{{ $labels['unmute'] }}">
            <svg class="studio-video__volume-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4zm12.2-.8a5.4 5.4 0 0 1 0 7.6l1.4 1.4a7.4 7.4 0 0 0 0-10.4l-1.4 1.4z" /></svg>
            <svg class="studio-video__muted-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4zm12.4 0L15 10.4l1.6 1.6-1.6 1.6 1.4 1.4 1.6-1.6 1.6 1.6 1.4-1.4-1.6-1.6 1.6-1.6L19.6 9 18 10.6 16.4 9z" /></svg>
        </button>

        <button class="studio-video__button" type="button" data-video-fullscreen aria-label="{{ $labels['fullscreen'] }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9V5h4v2H7v2H5zm10-4h4v4h-2V7h-2V5zM7 15v2h2v2H5v-4h2zm10 2v-2h2v4h-4v-2h2z" /></svg>
        </button>
    </div>
</div>
