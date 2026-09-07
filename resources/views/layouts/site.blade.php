@php
use App\Support\Studio;
$locale=app()->getLocale();$isHome=$isHome??false;$seo=$seo??\App\Support\Seo::make();$otherLocale=$locale==='ar'?'en':'ar';$alternate=$seo['alternates'][$otherLocale]??route('home',['locale'=>$otherLocale]);$loader=\App\Support\Preloader::make();
$favicon=$seo['logo']?:\App\Models\Asset::query()->where('kind','image')->where('visibility','public')->where('is_active',true)->find(Studio::setting('general.favicon'));$faviconPath=$favicon?->imageUrl(320);$faviconUrl=$faviconPath?(preg_match('~^https?://~i',$faviconPath)?$faviconPath:url($faviconPath)):asset('brand/mark.svg');
@endphp
<!doctype html><html lang="{{ $locale }}" dir="{{ $locale==='ar'?'rtl':'ltr' }}" data-theme="{{ \App\Support\Studio::setting('design.default_theme','light') }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="{{ Studio::color(Studio::setting('design.primary'),'#0A3323') }}">
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}"><meta name="keywords" content="{{ $seo['keywords'] }}"><meta name="author" content="{{ $seo['site_name'] }}"><meta name="robots" content="{{ $seo['index']?'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1':'noindex,nofollow' }}"><link rel="canonical" href="{{ $seo['canonical'] }}">
@foreach($seo['alternates'] as $lang=>$url)<link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $seo['alternates']['ar']??route('home',['locale'=>'ar']) }}">
<meta property="og:title" content="{{ $seo['title'] }}"><meta property="og:description" content="{{ $seo['description'] }}"><meta property="og:type" content="{{ $seo['og_type'] }}"><meta property="og:url" content="{{ $seo['canonical'] }}"><meta property="og:site_name" content="{{ $seo['site_name'] }}"><meta property="og:locale" content="{{ $locale==='ar'?'ar_EG':'en_US' }}"><meta property="og:locale:alternate" content="{{ $locale==='ar'?'en_US':'ar_EG' }}">
<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="{{ $seo['title'] }}"><meta name="twitter:description" content="{{ $seo['description'] }}">
@if($seo['image_url'])<meta property="og:image" content="{{ $seo['image_url'] }}"><meta property="og:image:alt" content="{{ $seo['image_alt'] }}"><meta name="twitter:image" content="{{ $seo['image_url'] }}"><meta name="twitter:image:alt" content="{{ $seo['image_alt'] }}">@endif
@if(($record??null) instanceof \App\Models\Post)<meta property="article:published_time" content="{{ ($record->published_at??$record->created_at)?->toIso8601String() }}"><meta property="article:modified_time" content="{{ $record->updated_at?->toIso8601String() }}">@endif
<script type="application/ld+json">{!! json_encode($seo['schema'],JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP) !!}</script>
@unless($isHome)<script type="application/ld+json">{!! json_encode($seo['breadcrumb'],JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP) !!}</script>@endunless
<link rel="icon" href="{{ $faviconUrl }}" type="{{ $favicon?'image/webp':'image/svg+xml' }}"><link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@include('site.partials.tokens')
<style>html{background:var(--canvas,#f7f4d5)}body{margin:0;color:var(--ink,#0a3323)}[x-cloak]{display:none!important}.studio-loader{position:fixed;inset:0;z-index:1000;display:grid;place-items:center;overflow:hidden}.studio-loader__background,.studio-loader__overlay{position:absolute;inset:0}.studio-loader__content{position:relative;z-index:2;display:grid;place-items:center}.studio-loader__logo{display:block;max-width:60vw;height:auto}@media(prefers-reduced-motion:reduce){.studio-loader{display:none!important}}</style>
<script data-navigate-once>try{let t=localStorage.getItem('studio-theme');if(t==='dark'||t==='light')document.documentElement.dataset.theme=t;}catch{}</script>
@vite(['resources/css/app.css','resources/css/footer.css','resources/js/app.js']) @livewireStyles
</head>
<body data-home="{{ $isHome?'1':'0' }}" data-transitions="{{ $loader['transitions']?'1':'0' }}" data-transition-duration="{{ $loader['duration'] }}" data-loader-delay="{{ $loader['minimum_duration'] }}">
<a href="#main" class="skip-link">@t('skip')</a>
@if($loader['enabled'])@include('site.partials.loader',['loader'=>$loader,'mode'=>'preloader'])@endif
@if($loader['transitions'])@include('site.partials.loader',['loader'=>$loader,'mode'=>'transition'])@endif
@include('site.partials.nav')
<main id="main" tabindex="-1">@yield('content')</main>
@include('site.partials.footer')
@include('site.partials.whatsapp-float')
<dialog class="lightbox" id="gallery-lightbox" aria-label="{{ \App\Support\Studio::text('project_gallery') }}"><div class="lightbox-head"><span></span><button type="button" class="lightbox-close" aria-label="{{ \App\Support\Studio::text('close') }}">×</button></div><div class="lightbox-content"></div><div class="lightbox-controls"><button type="button" data-lightbox-prev aria-label="{{ \App\Support\Studio::text('previous') }}">←</button><span data-lightbox-count></span><button type="button" data-lightbox-next aria-label="{{ \App\Support\Studio::text('next') }}">→</button></div></dialog>
@if(\App\Support\Studio::setting('scripts.enabled',false)&&!config('studio.demo'))@include('site.partials.consent')@endif
@include('site.partials.toasts')
@livewireScripts
</body></html>
