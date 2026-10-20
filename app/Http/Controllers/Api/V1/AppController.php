<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Middleware\SetApiLocale;
use App\Http\Resources\Api\V1\MediaResource;
use App\Http\Resources\Api\V1\MethodologyStepResource;
use App\Http\Resources\Api\V1\PartnerResource;
use App\Http\Resources\Api\V1\PostResource;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Http\Resources\Api\V1\SlideResource;
use App\Http\Resources\Api\V1\TestimonialResource;
use App\Models\Asset;
use App\Models\MethodologyStep;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Support\SettingsRegistry;
use App\Support\Studio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppController extends ApiController
{
    /** Design tokens with the same fallbacks as the website. */
    private const COLORS = ['primary' => '#0A3323', 'secondary' => '#105666', 'accent' => '#839958', 'warm' => '#D3968C', 'beige' => '#F7F4D5', 'light_background' => '#F7F4D5', 'light_surface' => '#FFFDF4', 'light_text' => '#0A3323', 'dark_background' => '#071F17', 'dark_surface' => '#0A3323', 'dark_text' => '#F7F4D5', 'dark_link' => '#A9D3C8'];

    /** Everything the app needs at launch: brand, colours, contact details and lead-form options. */
    public function config(): JsonResponse
    {
        $general = SettingsRegistry::groups()['general'];
        $whatsapp = Studio::whatsappNumber();
        $socials = collect(Studio::setting('general.socials', $general['socials'][3]) ?: [])
            ->filter(fn (mixed $link): bool => is_array($link) && filled($link['url'] ?? null) && Studio::safeUrl($link['url']) !== '#')
            ->map(fn (array $link): array => ['label' => (string) ($link['label'] ?? ''), 'url' => Studio::safeUrl($link['url'])])
            ->values();

        return response()->json(['data' => [
            'locale' => app()->getLocale(),
            'locales' => SetApiLocale::LOCALES,
            'site' => [
                'name' => Studio::translated('general.site_name', $general['site_name'][3][app()->getLocale()]),
                'tagline' => Studio::translated('general.tagline', $general['tagline'][3][app()->getLocale()]),
                'footer_text' => Studio::translated('general.footer_text', $general['footer_text'][3][app()->getLocale()]),
                'address' => Studio::translated('general.address', $general['address'][3][app()->getLocale()]),
                'website_url' => route('home', ['locale' => app()->getLocale()]),
            ],
            'logos' => [
                'light' => MediaResource::forAsset(Asset::publicImage(Studio::setting('general.logo_light'))),
                'dark' => MediaResource::forAsset(Asset::publicImage(Studio::setting('general.logo_dark'))),
                'icon' => MediaResource::forAsset(Asset::publicImage(Studio::setting('general.favicon'))),
            ],
            'contact' => [
                'email' => Studio::setting('general.email', $general['email'][3]),
                'phone' => Studio::setting('general.phone', $general['phone'][3]),
                'whatsapp' => $whatsapp,
                'whatsapp_url' => $whatsapp ? 'https://wa.me/'.$whatsapp : null,
            ],
            'socials' => $socials,
            'theme' => [
                'default' => Studio::setting('design.default_theme', 'light') === 'dark' ? 'dark' : 'light',
                'colors' => collect(self::COLORS)->map(fn (string $fallback, string $key): string => Studio::color(Studio::setting('design.'.$key), $fallback)),
            ],
            'lead_form' => [
                'services' => Service::published()->orderBy('sort_order')->get()->map(fn (Service $service): array => ['id' => $service->id, 'name' => $service->titleText()])->values(),
                'consent_text' => Studio::text('privacy_consent'),
            ],
        ]]);
    }

    /** All interface texts for the requested language, including overrides edited in the dashboard. */
    public function translations(): JsonResponse
    {
        $locale = app()->getLocale();
        $texts = (array) __('studio');
        foreach (Studio::translations() as $key => $values) {
            if (isset($values[$locale])) {
                $texts[$key] = $values[$locale];
            }
        }
        ksort($texts);

        return response()->json(['data' => $texts]);
    }

    /** The home screen in one request, mirroring the website's home page. */
    public function home(): JsonResponse
    {
        $slider = Slider::activeHomeHero()->with(['slides' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->with(SlideResource::WITH)])->first();
        $introMedia = Studio::setting('home.intro_media_enabled', true) ? Asset::publicImage(Studio::setting('home.intro_media')) : null;
        $introLink = Studio::safeUrl(str_replace('{locale}', app()->getLocale(), (string) Studio::setting('home.intro_link_url', '/{locale}/about')));
        $partners = Partner::query()->where('is_active', true)->with('image.media')->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn (Partner $partner): bool => filled($partner->titleText()) || MediaResource::isPublic($partner->image) || filled($partner->url))
            ->values();

        return response()->json(['data' => [
            'hero' => $slider ? [
                'autoplay' => (bool) $slider->autoplay,
                'autoplay_speed' => (int) $slider->autoplay_speed,
                'loop' => (bool) $slider->loop,
                'slides' => SlideResource::collection($slider->slides),
            ] : null,
            'intro' => Studio::setting('home.intro_enabled', true) ? [
                'label' => Studio::text('home_intro_label'),
                'title' => str_replace('\\n', "\n", Studio::text('home_intro_title')),
                'text' => str_replace('\\n', "\n", Studio::text('home_intro_text')),
                'media' => MediaResource::forAsset($introMedia),
                'media_caption' => str_replace('\\n', "\n", Studio::text('home_intro_media_caption')),
                'link' => Studio::setting('home.intro_link_enabled', true) && $introLink !== '#' ? ['text' => Studio::text('home_intro_link_text'), 'url' => $introLink] : null,
            ] : null,
            'sections' => [
                'services' => $this->section('what_we_do', 'services_title', 'services_intro'),
                'projects' => $this->section('selected_work', 'projects_title', 'projects_intro'),
                'methodology' => $this->section('our_process', 'methodology_title', 'methodology_intro'),
                'testimonials' => $this->section('client_words', 'testimonials_title', 'testimonials_intro'),
                'posts' => $this->section('insights', 'posts_title', 'posts_intro'),
                'partners' => $this->section('partners_label', 'partners_title', 'partners_intro'),
            ],
            'services' => ServiceResource::collection(Service::published()->with(ServiceResource::WITH)->orderByDesc('is_featured')->orderBy('sort_order')->limit(6)->get()),
            'projects' => ProjectResource::collection(Project::published()->with(ProjectResource::WITH)->orderByDesc('is_featured')->orderBy('sort_order')->latest()->limit(9)->get()),
            'methodology' => MethodologyStepResource::collection(MethodologyStep::published()->orderBy('sort_order')->get()),
            'testimonials' => TestimonialResource::collection(Testimonial::published()->where('is_demo', false)->with(TestimonialResource::WITH)->orderByDesc('is_featured')->orderBy('sort_order')->limit(6)->get()),
            'posts' => PostResource::collection(Post::published()->with(PostResource::WITH)->latest('published_at')->limit(3)->get()),
            'partners' => PartnerResource::collection($partners),
        ]]);
    }

    /** Searches services, projects and posts at once (`q`, at least 2 characters). */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);
        $term = '%'.addcslashes(trim((string) $request->query('q')), '%_\\').'%';
        $locale = app()->getLocale();

        return response()->json(['data' => [
            'services' => ServiceResource::collection(Service::published()->with(ServiceResource::WITH)->where('name->'.$locale, 'like', $term)->orderBy('sort_order')->limit(5)->get()),
            'projects' => ProjectResource::collection(Project::published()->with(ProjectResource::WITH)->where('title->'.$locale, 'like', $term)->orderByDesc('is_featured')->orderBy('sort_order')->limit(5)->get()),
            'posts' => PostResource::collection(Post::published()->with(PostResource::WITH)->where('title->'.$locale, 'like', $term)->latest('published_at')->limit(5)->get()),
        ]]);
    }

    /** @return array{label: string, title: string, description: string} */
    private function section(string $label, string $title, string $description): array
    {
        return ['label' => Studio::text($label), 'title' => Studio::text($title), 'description' => Studio::text($description)];
    }
}
