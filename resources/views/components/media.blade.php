@props(['asset'=>null,'priority'=>false,'sizes'=>'(max-width: 700px) 100vw, 50vw','class'=>'','poster'=>null,'controls'=>true,'deferVideo'=>false])
@if($asset && $asset->is_active && $asset->visibility==='public')
@if($asset->kind==='image')
<picture class="{{ $class }} media-picture" @if($asset->metadata['lqip']??null)style="background-image:url('{{ $asset->metadata['lqip'] }}')"@endif>
@if(count($asset->metadata['avif']??[]))<source type="image/avif" srcset="{{ collect($asset->metadata['avif'])->map(fn($p,$w)=>asset('storage/'.$p).' '.$w.'w')->join(', ') }}" sizes="{{ $sizes }}">@endif
<img src="{{ $asset->imageUrl($priority?1440:960) }}" @if(count($asset->metadata['webp']??[]))srcset="{{ collect($asset->metadata['webp'])->map(fn($p,$w)=>asset('storage/'.$p).' '.$w.'w')->join(', ') }}" sizes="{{ $sizes }}"@endif alt="{{ $asset->text('alt') }}" width="{{ $asset->metadata['width']??1440 }}" height="{{ $asset->metadata['height']??960 }}" loading="{{ $priority?'eager':'lazy' }}" decoding="async" @if($priority)fetchpriority="high"@endif>
</picture>
@elseif($asset->kind==='video')<video class="{{ $class }}" @if($controls)controls @endif muted playsinline preload="{{ $deferVideo?'none':'metadata' }}" @if($poster)poster="{{ $poster->imageUrl(1440) }}"@endif width="1440" height="900"><source {{ $deferVideo?'data-src':'src' }}="{{ $asset->publicUrl() }}" type="{{ $asset->metadata['mime']??'video/mp4' }}"></video>
@endif @endif