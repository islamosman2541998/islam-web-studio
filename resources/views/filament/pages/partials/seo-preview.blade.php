@php
    $settings = (array) $get('seo');
    $defaults = \App\Support\SettingsRegistry::groups()['seo'];
    $titles = is_array($settings['title'] ?? null) ? $settings['title'] : $defaults['title'][3];
    $descriptions = is_array($settings['description'] ?? null) ? $settings['description'] : $defaults['description'][3];
    $logoId = $settings['logo'] ?? null;
    $logo = \App\Models\Asset::query()->where('kind','image')->where('visibility','public')->where('is_active',true)->find(is_numeric($logoId)?(int)$logoId:null);
    $logoUrl = $logo?->imageUrl(320) ?: asset('brand/mark.svg');
    $siteUrl = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url');
@endphp
<section class="studio-settings-preview studio-seo-preview" wire:key="seo-preview-{{ md5(json_encode([$titles,$descriptions,$logoId])) }}">
    <div class="studio-settings-preview__heading"><div><span>{{ \App\Support\Studio::text('live_preview') }}</span><h3>{{ \App\Support\Studio::text('seo_preview_title') }}</h3><p>{{ \App\Support\Studio::text('seo_preview_help') }}</p></div></div>
    <div class="studio-seo-preview__grid">
        @foreach(['ar'=>['العربية','rtl'],'en'=>['English','ltr']] as $language=>[$label,$direction])
            <article class="studio-google-result" dir="{{ $direction }}">
                <small>{{ $label }}</small>
                <div class="studio-google-result__source"><span><img src="{{ $logoUrl }}" alt=""></span><div><b>{{ $language==='ar'?'إسلام ويب ستوديو':'Islam Web Studio' }}</b><em>{{ $siteUrl }}</em></div></div>
                <h4>{{ trim((string)($titles[$language]??'')) ?: $defaults['title'][3][$language] }}</h4>
                <p>{{ trim((string)($descriptions[$language]??'')) ?: $defaults['description'][3][$language] }}</p>
            </article>
        @endforeach
    </div>
</section>
