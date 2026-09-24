<div class="space-y-5 text-sm">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            __('studio.source') => ($session->utm_source ?: $session->referrer_host ?: __('studio.direct')),
            __('studio.device') => __('studio.device_'.$session->device_type).' · '.$session->browser,
            __('studio.operating_system') => $session->operating_system,
            __('studio.country') => ($session->country_code ?: '—'),
            __('studio.language') => ($session->language ?: '—'),
            __('studio.campaign') => ($session->utm_campaign ?: '—'),
        ] as $label => $value)
            <div class="rounded-xl border border-gray-200 p-3 dark:border-white/10">
                <div class="text-xs text-gray-500">{{ $label }}</div>
                <div class="mt-1 font-semibold break-words">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div>
        <h3 class="mb-3 font-bold">{{ __('studio.visitor_journey') }}</h3>
        <div class="space-y-3">
            @forelse ($session->pageViews as $view)
                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <div class="font-semibold break-all">{{ $view->path }}</div>
                            @if ($view->title)<div class="mt-1 text-xs text-gray-500">{{ $view->title }}</div>@endif
                        </div>
                        <div class="text-xs text-gray-500">{{ $view->viewed_at?->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-500">
                        <span>{{ __('studio.duration') }}: {{ gmdate('i:s', (int) $view->duration_seconds) }}</span>
                        <span>{{ __('studio.scroll_depth') }}: {{ $view->scroll_depth }}%</span>
                    </div>
                    @if ($view->events->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($view->events as $event)
                                <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                    {{ __('studio.event_'.$event->name) }}@if($event->label): {{ $event->label }}@endif
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-gray-500">{{ __('studio.no_visitor_activity') }}</div>
            @endforelse
        </div>
    </div>
</div>
