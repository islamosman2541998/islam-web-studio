@props(['service', 'number' => 1])
<a {{ $attributes->class('service-card') }} href="{{ $service->publicUrl() }}" wire:navigate data-reveal style="--reveal-delay: {{ min(($number - 1) * 70, 350) }}ms">
    <figure class="service-card-media">
        @if($service->imageMedia)
            <x-media :asset="$service->imageMedia" sizes="(max-width: 767px) 100vw, (max-width: 1100px) 50vw, 33vw" />
        @else
            <span class="service-card-placeholder" aria-hidden="true"></span>
        @endif
    </figure>
    <div class="service-card-body">
        <h3>{{ $service->titleText() }}</h3>
        <p>{{ $service->text('short_description') }}</p>
        <span class="card-link">@t('explore_service') <x-arrow-up-right /></span>
    </div>
</a>
