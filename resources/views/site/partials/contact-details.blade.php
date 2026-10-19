@php
    $email = trim((string) \App\Support\Studio::setting('general.email', ''));
    $phone = trim((string) \App\Support\Studio::setting('general.phone', ''));
    $phoneHref = preg_replace('/[^+0-9]/', '', $phone);
    $socials = collect(\App\Support\Studio::setting('general.socials', []))
        ->filter(fn ($social) => is_array($social)
            && filled($social['label'] ?? null)
            && \App\Support\Studio::safeUrl($social['url'] ?? null) !== '#');
@endphp

<div class="contact-details">
    @if($email)
        <a class="contact-detail-item" href="mailto:{{ $email }}">
            <span class="contact-detail-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
            </span>
            <span class="contact-detail-copy"><small>@t('email')</small><strong dir="ltr">{{ $email }}</strong></span>
        </a>
    @endif

    @if($phone && $phoneHref)
        <a class="contact-detail-item" href="tel:{{ $phoneHref }}">
            <span class="contact-detail-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7.2 3h3l1.4 4.2-2 1.4a15.5 15.5 0 0 0 5.8 5.8l1.4-2L21 14v3a4 4 0 0 1-4 4C9.3 20.5 3.5 14.7 3 7a4 4 0 0 1 4.2-4Z"/></svg>
            </span>
            <span class="contact-detail-copy"><small>@t('phone')</small><strong dir="ltr">{{ $phone }}</strong></span>
        </a>
    @endif

    @if($socials->isNotEmpty())
        <div class="contact-social-block">
            <span class="contact-social-title">@t('footer_follow')</span>
            <div class="contact-social-links">
                @foreach($socials as $social)
                    <a href="{{ \App\Support\Studio::safeUrl($social['url']) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}">
                        @include('site.partials.footer-social-icon', ['platform' => $social['label']])
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
