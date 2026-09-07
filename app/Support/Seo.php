<?php

namespace App\Support;

use App\Models\Asset;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\StudioRecord;

class Seo
{
    public static function make(?StudioRecord $record = null, ?string $title = null, ?string $description = null): array
    {
        $locale = app()->getLocale();
        $routeName = request()->route()?->getName();
        $site = self::translatedSetting('general', 'site_name');
        $defaultTitle = self::translatedSetting('seo', 'title');
        $defaultDescription = self::translatedSetting('seo', 'description');
        $recordTitle = $record?->titleText();
        $isHome = $routeName === 'home';

        $title = self::cleanText(
            $record?->text('meta_title')
            ?: ($isHome ? $defaultTitle : ($title ?: $recordTitle ?: $defaultTitle))
        );
        if ($site !== '' && ! str_contains(mb_strtolower($title), mb_strtolower($site))) {
            $title .= ' — '.$site;
        }

        $recordDescription = $record?->text('meta_description')
            ?: $record?->text('excerpt')
            ?: $record?->text('short_description')
            ?: $record?->text('overview');
        $description = self::cleanText(
            $isHome
                ? ($record?->text('meta_description') ?: $defaultDescription)
                : ($description ?: $recordDescription ?: $defaultDescription)
        );
        $canonical = $record?->canonical ? Studio::safeUrl($record->canonical) : url()->current();
        if ($canonical === '#') {
            $canonical = url()->current();
        }

        $alternates = self::alternates($record);
        $image = self::sharingImage($record);
        $logo = self::searchLogo();
        $imageUrl = self::assetUrl($image, 1440);
        $logoUrl = self::assetUrl($logo, 640);
        $index = (bool) Studio::setting('seo.index', true) && ($record?->seo_index ?? true) && ! config('studio.demo');
        $pageName = $recordTitle ?: $title;
        $organizationId = url('/').'#organization';
        $websiteId = url('/').'#website';
        $webpageId = $canonical.'#webpage';

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => $site,
            'url' => route('home', ['locale' => $locale]),
            'description' => $defaultDescription,
            'logo' => $logoUrl ? ['@type' => 'ImageObject', 'url' => $logoUrl] : null,
            'image' => $imageUrl,
            'email' => Studio::setting('general.email', SettingsRegistry::groups()['general']['email'][3]),
            'telephone' => Studio::setting('general.phone', SettingsRegistry::groups()['general']['phone'][3]),
            'sameAs' => self::socialLinks(),
        ], static fn (mixed $value): bool => filled($value));

        $website = array_filter([
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'url' => route('home', ['locale' => $locale]),
            'name' => $site,
            'alternateName' => self::translatedSetting('general', 'tagline'),
            'inLanguage' => ['ar', 'en'],
            'publisher' => ['@id' => $organizationId],
        ], static fn (mixed $value): bool => filled($value));

        $webpage = array_filter([
            '@type' => str_ends_with((string) $routeName, '.index') || str_ends_with((string) $routeName, '.category') ? 'CollectionPage' : 'WebPage',
            '@id' => $webpageId,
            'url' => $canonical,
            'name' => $pageName,
            'description' => $description,
            'inLanguage' => $locale,
            'isPartOf' => ['@id' => $websiteId],
            'about' => ['@id' => $organizationId],
            'primaryImageOfPage' => $imageUrl ? ['@type' => 'ImageObject', 'url' => $imageUrl] : null,
        ], static fn (mixed $value): bool => filled($value));

        $graph = [$organization, $website, $webpage];
        if ($entity = self::entity($record, $description, $canonical, $imageUrl, $organizationId, $webpageId)) {
            $graph[] = $entity;
        }

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => Studio::text('home'), 'item' => route('home', ['locale' => $locale])],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $pageName, 'item' => $canonical],
            ],
        ];

        return [
            'site_name' => $site,
            'title' => $title,
            'description' => $description,
            'keywords' => $isHome ? self::translatedSetting('seo', 'keywords') : ($record?->text('keywords') ?: self::translatedSetting('seo', 'keywords')),
            'canonical' => $canonical,
            'alternates' => $alternates,
            'image' => $image,
            'image_url' => $imageUrl,
            'image_alt' => $image?->text('alt') ?: $pageName,
            'logo' => $logo,
            'logo_url' => $logoUrl,
            'index' => $index,
            'og_type' => $record instanceof Post ? 'article' : 'website',
            'schema' => ['@context' => 'https://schema.org', '@graph' => $graph],
            'breadcrumb' => $breadcrumb,
        ];
    }

    private static function cleanText(?string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $value)));
    }

    private static function translatedSetting(string $group, string $key): string
    {
        $default = SettingsRegistry::groups()[$group][$key][3] ?? [];
        $fallback = is_array($default) ? (string) ($default[app()->getLocale()] ?? '') : (string) $default;

        return Studio::translated($group.'.'.$key, $fallback);
    }

    /** @return array<string, string> */
    private static function alternates(?StudioRecord $record): array
    {
        $alternates = [];
        foreach (['ar', 'en'] as $lang) {
            if ($record && isset($record->definition()['public'])) {
                if (! $record->text('slug', $lang) || ! $record->titleText($lang)) {
                    continue;
                }
                $alternates[$lang] = $record->publicUrl($lang);

                continue;
            }

            if ($routeName = request()->route()?->getName()) {
                $alternates[$lang] = route($routeName, array_merge(request()->route()->parameters(), ['locale' => $lang]));
            }
        }

        return $alternates;
    }

    private static function sharingImage(?StudioRecord $record): ?Asset
    {
        $recordImage = match (true) {
            $record instanceof Project => $record->mainMedia,
            $record instanceof Service => $record->imageMedia,
            $record instanceof Post => $record->featuredMedia,
            $record instanceof Page => $record->heroMedia,
            default => null,
        };

        return self::imageAsset($record?->og_image_id) ?: self::imageAsset($recordImage?->getKey()) ?: self::imageAsset(Studio::setting('seo.og_image'));
    }

    private static function searchLogo(): ?Asset
    {
        return self::imageAsset(Studio::setting('seo.logo'))
            ?: self::imageAsset(Studio::setting('general.logo_light'))
            ?: self::imageAsset(Studio::setting('general.favicon'));
    }

    private static function imageAsset(mixed $id): ?Asset
    {
        return Asset::query()
            ->where('kind', 'image')
            ->where('visibility', 'public')
            ->where('is_active', true)
            ->find(is_numeric($id) ? (int) $id : null);
    }

    private static function assetUrl(?Asset $asset, int $width): ?string
    {
        $path = $asset?->imageUrl($width);
        if (! filled($path)) {
            return null;
        }

        return preg_match('~^https?://~i', $path) ? $path : url($path);
    }

    /** @return array<int, string> */
    private static function socialLinks(): array
    {
        return collect(Studio::setting('general.socials', SettingsRegistry::groups()['general']['socials'][3]))
            ->pluck('url')
            ->filter(fn (mixed $url): bool => is_string($url) && preg_match('~^https?://~i', $url) === 1)
            ->values()
            ->all();
    }

    /** @return array<string, mixed>|null */
    private static function entity(?StudioRecord $record, string $description, string $canonical, ?string $imageUrl, string $organizationId, string $webpageId): ?array
    {
        if ($record instanceof Post) {
            return array_filter([
                '@type' => 'BlogPosting',
                '@id' => $canonical.'#article',
                'headline' => $record->titleText(),
                'description' => $description,
                'datePublished' => ($record->published_at ?? $record->created_at)?->toIso8601String(),
                'dateModified' => $record->updated_at?->toIso8601String(),
                'author' => ['@type' => 'Person', 'name' => $record->author?->name ?: self::translatedSetting('general', 'site_name')],
                'publisher' => ['@id' => $organizationId],
                'mainEntityOfPage' => ['@id' => $webpageId],
                'image' => $imageUrl,
            ], static fn (mixed $value): bool => filled($value));
        }

        if ($record instanceof Service) {
            return [
                '@type' => 'Service',
                '@id' => $canonical.'#service',
                'name' => $record->titleText(),
                'description' => $description,
                'provider' => ['@id' => $organizationId],
                'url' => $canonical,
            ];
        }

        if ($record instanceof Project) {
            return array_filter([
                '@type' => 'CreativeWork',
                '@id' => $canonical.'#project',
                'name' => $record->titleText(),
                'description' => $description,
                'creator' => ['@id' => $organizationId],
                'url' => $canonical,
                'image' => $imageUrl,
            ], static fn (mixed $value): bool => filled($value));
        }

        return null;
    }
}
