@php
    $isPreloader = $mode === 'preloader';
    $backgroundImage = $loader['background_type'] === 'image' && $loader['background_image_url']
        ? 'url('.json_encode($loader['background_image_url'], JSON_UNESCAPED_SLASHES).')'
        : 'none';
    $style = implode(';', [
        '--loader-background-color:'.$loader['background_color'],
        '--loader-background-image:'.$backgroundImage,
        '--loader-image-opacity:'.$loader['background_image_opacity'],
        '--loader-background-fit:'.$loader['background_fit'],
        '--loader-overlay:'.$loader['overlay_color'],
        '--loader-panel:'.$loader['panel_color'],
        '--loader-radius:'.$loader['panel_radius'].'px',
        '--loader-logo-width:'.$loader['logo_width'].'px',
        '--loader-text-color:'.$loader['text_color'],
        '--loader-text-size:'.$loader['text_size'].'px',
        '--loader-progress:'.$loader['progress_color'],
        '--loader-duration:'.$loader['duration'].'ms',
    ]);
@endphp
<div
    @if($isPreloader) id="preloader" @endif
    class="studio-loader {{ $isPreloader?'preloader':'page-transition' }}"
    data-animation="{{ $loader['animation'] }}"
    data-background="{{ $loader['background_type'] }}"
    aria-hidden="true"
    style="{{ $style }}"
>
    <div class="studio-loader__background"></div>
    @if($loader['background_type']==='image')<div class="studio-loader__overlay"></div>@endif
    <span class="studio-loader__orbit studio-loader__orbit--one"></span>
    <span class="studio-loader__orbit studio-loader__orbit--two"></span>
    <div class="studio-loader__content {{ $loader['panel_enabled']?'has-panel':'' }}">
        @if($loader['show_logo'])<img class="studio-loader__logo" src="{{ $loader['logo_url'] }}" width="{{ $loader['logo_width'] }}" alt="">@endif
        @if($loader['show_text']&&$loader['text']!=='')<p class="studio-loader__text">{{ $loader['text'] }}</p>@endif
        @if($loader['show_progress'])<span class="studio-loader__progress"><i></i></span>@endif
    </div>
</div>
