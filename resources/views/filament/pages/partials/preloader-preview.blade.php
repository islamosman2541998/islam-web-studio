@php
    $preview = \App\Support\Preloader::make((array) $get('preloader'));
    $backgroundImage = $preview['background_type'] === 'image' && $preview['background_image_url']
        ? 'url('.json_encode($preview['background_image_url'], JSON_UNESCAPED_SLASHES).')'
        : 'none';
    $previewStyle = implode(';', [
        '--preview-background:'.$preview['background_color'],
        '--preview-image:'.$backgroundImage,
        '--preview-image-opacity:'.$preview['background_image_opacity'],
        '--preview-fit:'.$preview['background_fit'],
        '--preview-overlay:'.$preview['overlay_color'],
        '--preview-panel:'.$preview['panel_color'],
        '--preview-radius:'.$preview['panel_radius'].'px',
        '--preview-logo-width:'.min(150, $preview['logo_width']).'px',
        '--preview-text-color:'.$preview['text_color'],
        '--preview-text-size:'.$preview['text_size'].'px',
        '--preview-progress:'.$preview['progress_color'],
    ]);
@endphp
<section class="studio-settings-preview" wire:key="preloader-preview-{{ md5(json_encode($preview)) }}">
    <div class="studio-settings-preview__heading">
        <div><span>{{ \App\Support\Studio::text('live_preview') }}</span><h3>{{ \App\Support\Studio::text('preloader_preview_title') }}</h3><p>{{ \App\Support\Studio::text('preloader_preview_help') }}</p></div>
    </div>
    <div class="studio-preloader-preview" data-animation="{{ $preview['animation'] }}" data-background="{{ $preview['background_type'] }}" style="{{ $previewStyle }}">
        <div class="studio-preloader-preview__background"></div>
        @if($preview['background_type']==='image')<div class="studio-preloader-preview__overlay"></div>@endif
        <span class="studio-preloader-preview__orbit studio-preloader-preview__orbit--one"></span><span class="studio-preloader-preview__orbit studio-preloader-preview__orbit--two"></span>
        <div class="studio-preloader-preview__content {{ $preview['panel_enabled']?'has-panel':'' }}">
            @if($preview['show_logo'])<img src="{{ $preview['logo_url'] }}" width="{{ min(150,$preview['logo_width']) }}" alt="">@endif
            @if($preview['show_text']&&$preview['text']!=='')<p>{{ $preview['text'] }}</p>@endif
            @if($preview['show_progress'])<span class="studio-preloader-preview__progress"><i></i></span>@endif
        </div>
    </div>
</section>
