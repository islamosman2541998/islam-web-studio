@php
    $footerMenu = \App\Support\MenuResolver::forLocation('footer') ?: \App\Support\MenuResolver::forLocation('navbar');
    $footerServices = \App\Models\Service::published()->orderBy('sort_order')->orderBy('id')->limit(6)->get();
    $email = trim((string) \App\Support\Studio::setting('general.email', ''));
    $phone = trim((string) \App\Support\Studio::setting('general.phone', ''));
    $phoneHref = preg_replace('/[^+0-9]/', '', $phone);
    $socials = collect(\App\Support\Studio::setting('general.socials', []))
        ->filter(fn ($social) => is_array($social) && filled($social['label'] ?? null) && \App\Support\Studio::safeUrl($social['url'] ?? null) !== '#');
@endphp

<footer class="site-footer">
    <div class="shell">
        <div class="footer-top">
            <section class="footer-column footer-brand-column" data-footer-column="brand" aria-label="{{ \App\Support\Studio::translated('general.site_name') }}">
                @include('site.partials.brand')
                <p>{{ \App\Support\Studio::translated('general.footer_text') }}</p>
                @if(\App\Support\Studio::translated('general.address'))
                    <span class="footer-location">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        {{ \App\Support\Studio::translated('general.address') }}
                    </span>
                @endif
            </section>

            <nav class="footer-column footer-links" data-footer-column="links" aria-labelledby="footer-links-title">
                <h2 id="footer-links-title">@t('footer_links')</h2>
                @include('site.partials.menu-items', ['items' => $footerMenu])
            </nav>

            <nav class="footer-column footer-services" data-footer-column="services" aria-labelledby="footer-services-title">
                <h2 id="footer-services-title">@t('services')</h2>
                <ul>
                    @foreach($footerServices as $service)
                        <li><a href="{{ $service->publicUrl() }}" wire:navigate>{{ $service->titleText() }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <section class="footer-column footer-contact" data-footer-column="contact" aria-labelledby="footer-contact-title">
                <h2 id="footer-contact-title">@t('footer_contact')</h2>
                <div class="footer-contact-list">
                    @if($email)
                        <a href="mailto:{{ $email }}"><small>@t('email')</small><span dir="ltr">{{ $email }}</span></a>
                    @endif
                    @if($phone && $phoneHref)
                        <a href="tel:{{ $phoneHref }}"><small>@t('phone')</small><span dir="ltr">{{ $phone }}</span></a>
                    @endif
                </div>
                @if($socials->isNotEmpty())
                    <div class="footer-social-block">
                        <span class="footer-social-title">@t('footer_follow')</span>
                        <div class="social-links">
                            @foreach($socials as $social)
                                <a href="{{ \App\Support\Studio::safeUrl($social['url']) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}">
                                    @include('site.partials.footer-social-icon', ['platform' => $social['label']])
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>

        <div class="footer-bottom">
            <span>© {{ now()->year }} Islam Web Studio. @t('copyright')</span>
            <span dir="ltr">THOUGHTFULLY MADE. PURPOSEFULLY BUILT.</span>
        </div>
    </div>
</footer>
