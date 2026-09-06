@php($socialPlatform = strtolower((string) $platform))
@if(str_contains($socialPlatform, 'facebook'))
    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14 8h3V4h-3c-3.3 0-5 2-5 5v2H6v4h3v6h4v-6h3.4l.6-4h-4V9c0-.7.3-1 1-1Z"/></svg>
@elseif(str_contains($socialPlatform, 'instagram'))
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.4" cy="6.7" r="1" fill="currentColor" stroke="none"/></svg>
@elseif(str_contains($socialPlatform, 'tiktok'))
    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15 3c.4 2.2 1.7 3.6 4 4v4a10 10 0 0 1-4-1.2V16a5 5 0 1 1-5-5v4a1 1 0 1 0 1 1V3h4Z"/></svg>
@else
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M10 14a4 4 0 0 0 5.7 0l2.8-2.8a4 4 0 1 0-5.7-5.7l-1.6 1.6M14 10a4 4 0 0 0-5.7 0l-2.8 2.8a4 4 0 1 0 5.7 5.7l1.6-1.6"/></svg>
@endif
