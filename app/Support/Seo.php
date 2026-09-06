<?php

namespace App\Support;

use App\Models\Asset;
use App\Models\Post;
use App\Models\Service;
use App\StudioRecord;

class Seo
{
    public static function make(?StudioRecord $record = null, ?string $title = null, ?string $description = null): array
    {
        $locale = app()->getLocale();
        $site = Studio::translated('general.site_name', 'Islam Web Studio');
        $title = ($record?->text('meta_title') ?: $title ?: $record?->titleText()) ?: Studio::translated('seo.title', $site);
        if (! str_contains($title, $site)) {
            $title .= ' — '.$site;
        }
        $description = strip_tags(($record?->text('meta_description') ?: $description ?: $record?->text('excerpt') ?: $record?->text('short_description') ?: $record?->text('overview')) ?: Studio::translated('seo.description'));
        $canonical = $record?->canonical ? Studio::safeUrl($record->canonical) : url()->current();
        if ($canonical === '#') {
            $canonical = url()->current();
        }
        $alternates = [];
        foreach (['ar', 'en'] as $lang) {
            if ($record && isset($record->definition()['public'])) {
                if (! $record->text('slug', $lang) || ! $record->titleText($lang)) {
                    continue;
                }$alternates[$lang] = $record->publicUrl($lang);
            } elseif (request()->route()?->getName()) {
                $alternates[$lang] = route(request()->route()->getName(), array_merge(request()->route()->parameters(), ['locale' => $lang]));
            }
        }
        $image = Asset::where('visibility', 'public')->find($record?->og_image_id ?: Studio::setting('seo.og_image'));
        $index = (bool) Studio::setting('seo.index', true) && ($record?->seo_index ?? true) && ! config('studio.demo');
        $schema = ['@context' => 'https://schema.org', '@type' => 'Organization', '@id' => url('/').'#studio', 'name' => $site, 'url' => route('home', ['locale' => $locale]), 'description' => $description];
        if ($record instanceof Post) {
            $schema = ['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $record->titleText(), 'description' => $description, 'datePublished' => ($record->published_at ?? $record->created_at)->toIso8601String(), 'dateModified' => $record->updated_at->toIso8601String(), 'author' => ['@type' => 'Person', 'name' => $record->author?->name ?? $site], 'publisher' => ['@type' => 'Organization', 'name' => $site], 'mainEntityOfPage' => $canonical];
            if ($image) {
                $schema['image'] = $image->imageUrl(1440);
            }
        }
        if ($record instanceof Service) {
            $schema = ['@context' => 'https://schema.org', '@type' => 'Service', 'name' => $record->titleText(), 'description' => $description, 'provider' => ['@type' => 'Organization', 'name' => $site], 'url' => $canonical];
        }
        $breadcrumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => Studio::text('home'), 'item' => route('home', ['locale' => $locale])], ['@type' => 'ListItem', 'position' => 2, 'name' => $record?->titleText() ?: $title, 'item' => $canonical]]];

        return compact('title', 'description', 'canonical', 'alternates', 'image', 'index', 'schema', 'breadcrumb') + ['keywords' => $record?->text('keywords') ?: Studio::translated('seo.keywords')];
    }
}
