<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\MethodologyStep;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Support\MenuResolver;
use App\Support\ModuleRegistry;
use App\Support\Seo;
use App\Support\Studio;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SiteController extends Controller
{
    private function find(string $class, string $slug, array $with = [])
    {
        $record = $class::published()->with($with)->where('slug->'.app()->getLocale(), $slug)->first();
        if (! $record) {
            $redirect = Redirect::where('from_path', '/'.request()->path())->where('is_active', true)->first();
            if ($redirect && $redirect->to_path !== $redirect->from_path && preg_match('~^/(ar|en)/~', $redirect->to_path)) {
                abort(response()->redirectTo($redirect->to_path, 301));
            }abort(404);
        }

        return $record;
    }

    public function home(string $locale)
    {
        $record = Page::published()->where('template', 'home')->first();
        $introMedia = Asset::query()
            ->where('kind', 'image')
            ->where('visibility', 'public')
            ->where('is_active', true)
            ->find(Studio::setting('home.intro_media'));

        return view('site.home', [
            'isHome' => true,
            'record' => $record,
            'seo' => Seo::make($record),
            'slider' => Slider::activeHomeHero()->with(['slides' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'), 'slides.desktopMedia', 'slides.mobileMedia', 'slides.desktopPoster', 'slides.mobilePoster'])->first(),
            'services' => Service::published()->with('imageMedia', 'serviceCategory')->orderByDesc('is_featured')->orderBy('sort_order')->limit(6)->get(),
            'projects' => Project::published()->with('mainMedia', 'mainMediaPoster', 'projectCategory', 'metrics', 'gallery.media')->orderByDesc('is_featured')->orderBy('sort_order')->limit(9)->get(),
            'introMedia' => $introMedia,
            'steps' => MethodologyStep::published()->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::published()->where('is_demo', false)->orderByDesc('is_featured')->orderBy('sort_order')->limit(6)->get(),
            'posts' => Post::published()->with('featuredMedia', 'postCategory')->latest('published_at')->limit(3)->get(),
        ]);
    }

    private function archive(string $module, $category = null)
    {
        return view('site.archive', ['module' => $module, 'category' => $category, 'seo' => Seo::make($category, Studio::text($module.'_title'), Studio::text($module.'_intro'))]);
    }

    public function services(string $locale)
    {
        return $this->archive('services');
    }

    public function projects(string $locale)
    {
        return $this->archive('projects');
    }

    public function posts(string $locale)
    {
        return $this->archive('posts');
    }

    public function serviceCategory(string $locale, string $slug)
    {
        return $this->archive('services', $this->find(ServiceCategory::class, $slug));
    }

    public function projectCategory(string $locale, string $slug)
    {
        return $this->archive('projects', $this->find(ProjectCategory::class, $slug));
    }

    public function postCategory(string $locale, string $slug)
    {
        return $this->archive('posts', $this->find(PostCategory::class, $slug));
    }

    public function service(string $locale, string $slug)
    {
        $record = $this->find(Service::class, $slug, ['imageMedia', 'serviceCategory', 'features', 'packages']);

        return view('site.service', ['record' => $record, 'seo' => Seo::make($record), 'projects' => $record->projects()->published()->with('mainMedia', 'mainMediaPoster', 'projectCategory', 'metrics', 'gallery.media')->limit(3)->get()]);
    }

    public function project(string $locale, string $slug)
    {
        $record = $this->find(Project::class, $slug, ['mainMedia', 'mainMediaPoster', 'mainMediaMobile', 'projectCategory', 'gallery.media', 'metrics', 'services', 'testimonial']);

        $related = Project::published()
            ->where('id', '!=', $record->id)
            ->with('mainMedia', 'mainMediaPoster', 'projectCategory', 'metrics', 'gallery.media')
            ->when(
                $record->project_category_id,
                fn ($query) => $query->orderByRaw('CASE WHEN project_category_id = ? THEN 0 ELSE 1 END', [$record->project_category_id])
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(9)
            ->get();

        return view('site.project', [
            'record' => $record,
            'seo' => Seo::make($record),
            'related' => $related,
        ]);
    }

    public function post(string $locale, string $slug)
    {
        $record = $this->find(Post::class, $slug, ['featuredMedia', 'postCategory', 'author', 'tags']);

        return view('site.post', ['record' => $record, 'seo' => Seo::make($record), 'related' => Post::published()->where('id', '!=', $record->id)->with('featuredMedia', 'postCategory')->latest('published_at')->limit(3)->get()]);
    }

    public function about(string $locale)
    {
        $record = Page::published()->with('heroMedia', 'gallery.media')->where('template', 'about')->firstOrFail();

        return view('site.page', ['record' => $record, 'seo' => Seo::make($record), 'isAbout' => true]);
    }

    public function methodology(string $locale)
    {
        return view('site.methodology', ['seo' => Seo::make(title: Studio::text('methodology_title')), 'steps' => MethodologyStep::published()->orderBy('sort_order')->get()]);
    }

    public function testimonials(string $locale)
    {
        return view('site.testimonials', ['seo' => Seo::make(title: Studio::text('testimonials_title')), 'testimonials' => Testimonial::published()->where('is_demo', false)->orderByDesc('is_featured')->orderBy('sort_order')->paginate(12)]);
    }

    public function contact(string $locale)
    {
        return view('site.contact', ['seo' => Seo::make(title: Studio::text('contact_title'))]);
    }

    public function page(string $locale, string $slug)
    {
        $record = $this->find(Page::class, $slug, ['heroMedia', 'heroPoster', 'gallery.media']);

        return view('site.page', ['record' => $record, 'seo' => Seo::make($record)]);
    }

    public function sitemap()
    {
        $xml = Cache::remember('studio.sitemap', 3600, function () {
            $map = Sitemap::create();
            foreach (['ar', 'en'] as $locale) {
                foreach (array_keys(MenuResolver::routeOptions()) as $route) {
                    $map->add(Url::create(route($route, ['locale' => $locale])));
                }foreach (ModuleRegistry::all() as $module => $definition) {
                    if (! isset($definition['public'])) {
                        continue;
                    }$class = ModuleRegistry::model($module);
                    $class::published($locale)->where('seo_index', true)->orderBy('id')->chunkById(200, function ($records) use ($map, $locale) {
                        foreach ($records as $record) {
                            $url = Url::create($record->publicUrl($locale))->setLastModificationDate($record->updated_at);
                            foreach (['ar', 'en'] as $alternate) {
                                if ($record->text('slug', $alternate) && $record->titleText($alternate)) {
                                    $url->addAlternate($record->publicUrl($alternate), $alternate);
                                }
                            }$map->add($url);
                        }
                    });
                }
            }

            return $map->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots()
    {
        return response((config('studio.demo') || ! Studio::setting('seo.index', true)) ? "User-agent: *\nDisallow: /\n" : "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /exports\nDisallow: /private-assets\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
