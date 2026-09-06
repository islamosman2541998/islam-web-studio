@php
use App\Support\Studio;
$locale=app()->getLocale();$isHome=$isHome??false;$seo=$seo??\App\Support\Seo::make();$otherLocale=$locale==='ar'?'en':'ar';$alternate=$seo['alternates'][$otherLocale]??route('home',['locale'=>$otherLocale]);
@endphp
<!doctype html><html lang="{{ $locale }}" dir="{{ $locale==='ar'?'rtl':'ltr' }}" data-theme="{{ \App\Support\Studio::setting('design.default_theme','light') }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0A3323">
<title>{{ $seo['title'] }}</title><meta name="description" content="{{ $seo['description'] }}"><meta name="keywords" content="{{ $seo['keywords'] }}"><meta name="robots" content="{{ $seo['index']?'index,follow':'noindex,nofollow' }}"><link rel="canonical" href="{{ $seo['canonical'] }}">
@foreach($seo['alternates'] as $lang=>$url)<link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $seo['alternates']['ar']??route('home',['locale'=>'ar']) }}">
<meta property="og:title" content="{{ $seo['title'] }}"><meta property="og:description" content="{{ $seo['description'] }}"><meta property="og:type" content="{{ ($record??null) instanceof \App\Models\Post?'article':'website' }}"><meta property="og:url" content="{{ $seo['canonical'] }}"><meta property="og:locale" content="{{ $locale==='ar'?'ar_EG':'en_US' }}"><meta name="twitter:card" content="summary_large_image">
@if($seo['image'])<meta property="og:image" content="{{ $seo['image']->imageUrl(1440) }}"><meta name="twitter:image" content="{{ $seo['image']->imageUrl(1440) }}">@endif
<script type="application/ld+json">{!! json_encode($seo['schema'],JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP) !!}</script>
@unless($isHome)<script type="application/ld+json">{!! json_encode($seo['breadcrumb'],JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP) !!}</script>@endunless
<link rel="icon" href="{{ ($fav=\App\Models\Asset::find(\App\Support\Studio::setting('general.favicon')))?$fav->imageUrl(320):asset('brand/mark.svg') }}" type="{{ $fav?'image/webp':'image/svg+xml' }}">
@include('site.partials.tokens')
<style>html{background:var(--canvas,#f7f4d5)}body{margin:0;color:var(--ink,#0a3323)}[x-cloak]{display:none!important}.preloader{position:fixed;inset:0;z-index:1000;display:grid;place-items:center;background:#0a3323;pointer-events:none;animation:preloader-out .4s 1.2s forwards}.preloader img{width:72px;height:84px}@keyframes preloader-out{to{opacity:0;visibility:hidden}}@media(prefers-reduced-motion:reduce){.preloader{display:none}}</style>
<script data-navigate-once>try{let t=localStorage.getItem('studio-theme');if(t==='dark'||t==='light')document.documentElement.dataset.theme=t;}catch{}</script>
@vite(['resources/css/app.css','resources/css/footer.css','resources/js/app.js']) @livewireStyles
</head>
<body data-home="{{ $isHome?'1':'0' }}" data-transitions="{{ \App\Support\Studio::setting('preloader.transitions',true)?'1':'0' }}" data-transition-duration="{{ max(100,min(1200,(int)\App\Support\Studio::setting('preloader.duration',400))) }}">
<a href="#main" class="skip-link">@t('skip')</a>
@if(\App\Support\Studio::setting('preloader.enabled',true))<div class="preloader" id="preloader" aria-hidden="true" data-animation="{{ \App\Support\Studio::setting('preloader.animation','fade') }}"><img src="{{ ($preLogo=\App\Models\Asset::find(\App\Support\Studio::setting('preloader.logo')))?$preLogo->imageUrl(320):asset('brand/mark-light.svg') }}" width="72" height="84" alt=""></div>@endif
<div class="page-transition" aria-hidden="true"><img src="{{ asset('brand/mark-light.svg') }}" width="40" height="46" alt=""></div>
@include('site.partials.nav')
<main id="main" tabindex="-1">@yield('content')</main>
@include('site.partials.footer')
<dialog class="lightbox" id="gallery-lightbox" aria-label="{{ \App\Support\Studio::text('project_gallery') }}"><div class="lightbox-head"><span></span><button type="button" class="lightbox-close" aria-label="{{ \App\Support\Studio::text('close') }}">×</button></div><div class="lightbox-content"></div><div class="lightbox-controls"><button type="button" data-lightbox-prev aria-label="{{ \App\Support\Studio::text('previous') }}">←</button><span data-lightbox-count></span><button type="button" data-lightbox-next aria-label="{{ \App\Support\Studio::text('next') }}">→</button></div></dialog>
@if(\App\Support\Studio::setting('scripts.enabled',false)&&!config('studio.demo'))@include('site.partials.consent')@endif
@include('site.partials.toasts')
@livewireScripts
</body></html>
