<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Lead;
use App\Models\MethodologyStep;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectMedia;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\Translation;
use App\Models\User;
use App\Support\Studio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The test client sends `Accept-Language: en-us` by default; a mobile client usually sends none.
        $this->withHeader('Accept-Language', '');
    }

    private function service(array $attributes = []): Service
    {
        return Service::factory()->create([
            'status' => 'published',
            'service_category_id' => ServiceCategory::factory()->create(['status' => 'published'])->id,
            ...$attributes,
        ]);
    }

    private function project(array $attributes = []): Project
    {
        return Project::factory()->create([
            'status' => 'published',
            'project_category_id' => ProjectCategory::factory()->create(['status' => 'published'])->id,
            ...$attributes,
        ]);
    }

    /** @return array<string, mixed> */
    private function lead(array $overrides = []): array
    {
        return [
            'name' => 'App visitor',
            'email' => 'visitor@example.test',
            'phone' => '+201000000000',
            'message' => 'I would like a mobile-friendly website for my company.',
            'consent' => true,
            ...$overrides,
        ];
    }

    public function test_config_returns_brand_contact_theme_and_lead_form_services(): void
    {
        $service = $this->service(['name' => ['ar' => 'تصميم مواقع', 'en' => 'Web design']]);
        $this->service(['status' => 'draft', 'name' => ['ar' => 'مسودة', 'en' => 'Draft service']]);

        $this->getJson('/api/v1/config?lang=en')
            ->assertOk()
            ->assertJsonPath('data.locale', 'en')
            ->assertJsonPath('data.locales', ['ar', 'en'])
            ->assertJsonPath('data.theme.colors.primary', '#0A3323')
            ->assertJsonPath('data.lead_form.services', [['id' => $service->id, 'name' => 'Web design']])
            ->assertJsonStructure(['data' => ['site' => ['name', 'tagline', 'website_url'], 'logos' => ['light', 'dark', 'icon'], 'contact' => ['email', 'phone', 'whatsapp', 'whatsapp_url'], 'socials']]);
    }

    public function test_language_follows_the_lang_query_then_the_accept_language_header(): void
    {
        $this->service(['name' => ['ar' => 'خدمة عربية', 'en' => 'English service']]);

        $this->getJson('/api/v1/services')->assertHeader('Content-Language', 'ar')->assertJsonPath('data.0.name', 'خدمة عربية');
        $this->getJson('/api/v1/services', ['Accept-Language' => 'en-US,en;q=0.9'])->assertHeader('Content-Language', 'en')->assertJsonPath('data.0.name', 'English service');
        $this->getJson('/api/v1/services?lang=ar', ['Accept-Language' => 'en'])->assertJsonPath('data.0.name', 'خدمة عربية');
        $this->getJson('/api/v1/services?lang=fr')->assertHeader('Content-Language', 'ar');
    }

    public function test_home_returns_every_section_in_one_request(): void
    {
        $this->service();
        $this->project();
        MethodologyStep::factory()->create();
        Testimonial::factory()->create(['is_approved' => true]);
        Testimonial::factory()->create(['is_approved' => true, 'is_demo' => true]);

        $this->getJson('/api/v1/home')
            ->assertOk()
            ->assertJsonCount(1, 'data.services')
            ->assertJsonCount(1, 'data.projects')
            ->assertJsonCount(1, 'data.methodology')
            ->assertJsonCount(1, 'data.testimonials')
            ->assertJsonStructure(['data' => ['hero', 'intro', 'sections' => ['services' => ['label', 'title', 'description'], 'projects', 'methodology', 'testimonials', 'posts', 'partners'], 'posts', 'partners']]);
    }

    public function test_lists_return_published_records_with_pagination_filters_and_sorting(): void
    {
        $category = ServiceCategory::factory()->create(['status' => 'published']);
        $featured = $this->service(['service_category_id' => $category->id, 'is_featured' => true, 'name' => ['ar' => 'متجر إلكتروني', 'en' => 'Online store']]);
        $this->service(['name' => ['ar' => 'هوية بصرية', 'en' => 'Brand identity']]);
        $this->service(['status' => 'draft']);
        $this->service(['is_active' => false]);

        $this->getJson('/api/v1/services?per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $featured->id)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonStructure(['links' => ['next'], 'meta' => ['current_page', 'last_page']]);

        $this->getJson('/api/v1/services?per_page=500')->assertJsonPath('meta.per_page', 50);
        $this->getJson('/api/v1/services?category='.$category->id)->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $featured->id);
        $this->getJson('/api/v1/services?category='.$category->text('slug', 'ar'))->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/services?category=missing')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/services?featured=1')->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/services?lang=en&search=store')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Online store');
        $this->getJson('/api/v1/services?lang=en&sort=name')->assertJsonPath('data.0.name', 'Brand identity');
        $this->getJson('/api/v1/service-categories')->assertJsonFragment(['id' => $category->id]);
    }

    public function test_project_detail_is_found_by_id_or_slug_with_gallery_and_related_work(): void
    {
        $image = Asset::factory()->create(['kind' => 'image']);
        $hidden = Asset::factory()->create(['kind' => 'image', 'visibility' => 'private']);
        $project = $this->project(['client' => 'Acme', 'tech_stack' => ['Laravel', 'Flutter'], 'challenge' => ['ar' => '<p>تحدي</p><script>alert(1)</script>', 'en' => '<p>Challenge</p>']]);
        ProjectMedia::create(['project_id' => $project->id, 'media_id' => $image->id, 'caption' => ['ar' => 'لقطة', 'en' => 'Screen'], 'sort_order' => 0]);
        ProjectMedia::create(['project_id' => $project->id, 'media_id' => $hidden->id, 'sort_order' => 1]);
        $project->metrics()->create(['label' => ['ar' => 'زيادة المبيعات', 'en' => 'More sales'], 'value' => '+40%', 'sort_order' => 0]);
        $related = $this->project();

        $response = $this->getJson('/api/v1/projects/'.$project->text('slug', 'en'))
            ->assertOk()
            ->assertJsonPath('data.id', $project->id)
            ->assertJsonPath('data.client', 'Acme')
            ->assertJsonPath('data.tech_stack', ['Laravel', 'Flutter'])
            ->assertJsonPath('data.highlight_metric', ['value' => '+40%', 'label' => 'زيادة المبيعات'])
            ->assertJsonCount(1, 'data.gallery')
            ->assertJsonPath('data.gallery.0.media.id', $image->id)
            ->assertJsonPath('data.gallery.0.caption', 'لقطة')
            ->assertJsonPath('related.0.id', $related->id);
        $this->assertStringNotContainsString('<script', $response->json('data.challenge_html'));

        $this->getJson('/api/v1/projects/'.$project->id)->assertOk()->assertJsonPath('data.id', $project->id);
    }

    public function test_unpublished_or_missing_records_return_a_json_404(): void
    {
        $draft = $this->project(['status' => 'draft']);

        $this->getJson('/api/v1/projects/'.$draft->id)->assertNotFound()->assertJsonPath('message', Studio::text('not_found'));
        $this->getJson('/api/v1/services/no-such-service')->assertNotFound();
        $this->getJson('/api/v1/about')->assertNotFound();
    }

    public function test_service_detail_includes_features_packages_and_related_projects(): void
    {
        $service = $this->service(['pricing_type' => 'packages']);
        $service->features()->create(['text' => ['ar' => 'تصميم متجاوب', 'en' => 'Responsive design'], 'sort_order' => 0]);
        $service->packages()->create(['name' => ['ar' => 'الأساسية', 'en' => 'Starter'], 'price' => 5000, 'currency' => 'EGP', 'features' => ['ar' => "5 صفحات\n- دومين مجاني", 'en' => "5 pages\n- Free domain"], 'sort_order' => 0]);
        $project = $this->project();
        $service->projects()->attach($project);

        $this->getJson('/api/v1/services/'.$service->id.'?lang=en')
            ->assertOk()
            ->assertJsonPath('data.features', ['Responsive design'])
            ->assertJsonPath('data.packages.0.name', 'Starter')
            ->assertJsonPath('data.packages.0.price', 5000)
            ->assertJsonPath('data.packages.0.features', ['5 pages', 'Free domain'])
            ->assertJsonPath('related_projects.0.id', $project->id);

        $this->getJson('/api/v1/projects?service='.$service->id)->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $project->id);
    }

    public function test_posts_are_newest_first_filter_by_tag_and_show_content(): void
    {
        $author = User::factory()->create(['name' => 'Islam']);
        $category = PostCategory::factory()->create(['status' => 'published']);
        $older = Post::factory()->create(['status' => 'published', 'post_category_id' => $category->id, 'author_id' => $author->id, 'published_at' => now()->subDays(3)]);
        $newer = Post::factory()->create(['status' => 'published', 'post_category_id' => $category->id, 'author_id' => $author->id, 'published_at' => now()->subDay()]);
        Post::factory()->create(['status' => 'published', 'post_category_id' => $category->id, 'author_id' => $author->id, 'published_at' => now()->addWeek()]);
        $tag = Tag::factory()->create(['is_active' => true]);
        $older->tags()->attach($tag);

        $this->getJson('/api/v1/posts')->assertJsonCount(2, 'data')->assertJsonPath('data.0.id', $newer->id);
        $this->getJson('/api/v1/posts?tag='.$tag->text('slug', 'ar'))->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $older->id);
        $this->getJson('/api/v1/posts/'.$older->id)
            ->assertOk()
            ->assertJsonPath('data.author.name', 'Islam')
            ->assertJsonPath('data.tags.0.id', $tag->id)
            ->assertJsonStructure(['data' => ['content_html', 'reading_minutes', 'published_at'], 'related']);
    }

    public function test_about_and_content_pages_are_available(): void
    {
        $about = Page::factory()->create(['status' => 'published', 'template' => 'about']);
        $page = Page::factory()->create(['status' => 'published', 'template' => 'standard']);
        Page::factory()->create(['status' => 'published', 'template' => 'home']);

        $this->getJson('/api/v1/about')->assertOk()->assertJsonPath('data.id', $about->id);
        $this->getJson('/api/v1/pages/'.$page->text('slug', 'ar'))->assertOk()->assertJsonStructure(['data' => ['content_html', 'gallery', 'downloads']]);
        $this->getJson('/api/v1/pages')->assertJsonCount(2, 'data');
    }

    public function test_hidden_media_is_never_exposed(): void
    {
        $private = Asset::factory()->create(['kind' => 'image', 'visibility' => 'private']);
        $inactive = Asset::factory()->create(['kind' => 'image', 'is_active' => false]);
        $this->service(['image_media_id' => $private->id]);
        $this->service(['image_media_id' => $inactive->id]);

        $this->getJson('/api/v1/services')->assertJsonPath('data.0.image', null)->assertJsonPath('data.1.image', null);
    }

    public function test_translations_include_dashboard_overrides(): void
    {
        Translation::create(['key' => 'request_quote', 'value' => ['ar' => 'اطلب عرض سعر الآن', 'en' => 'Get a quote now'], 'is_active' => true]);
        Studio::flush();

        $this->getJson('/api/v1/translations?lang=en')
            ->assertOk()
            ->assertJsonPath('data.request_quote', 'Get a quote now')
            ->assertJsonPath('data.not_found', 'Page not found');
    }

    public function test_search_covers_services_projects_and_posts(): void
    {
        $this->service(['name' => ['ar' => 'تطبيقات موبايل', 'en' => 'Mobile apps']]);
        $this->project(['title' => ['ar' => 'تطبيق توصيل', 'en' => 'Delivery app']]);

        $this->getJson('/api/v1/search?lang=en&q=app')
            ->assertOk()
            ->assertJsonCount(1, 'data.services')
            ->assertJsonCount(1, 'data.projects')
            ->assertJsonCount(0, 'data.posts');
        $this->getJson('/api/v1/search?q=a')->assertUnprocessable()->assertJsonValidationErrors('q');
    }

    public function test_app_lead_is_saved_for_the_dashboard_and_notifies_the_team(): void
    {
        Permission::findOrCreate('leads.view', 'web');
        $recipient = User::factory()->create(['is_active' => true]);
        $recipient->givePermissionTo('leads.view');
        $service = $this->service();

        $this->postJson('/api/v1/leads?lang=en', $this->lead(['service_id' => $service->id]))
            ->assertCreated()
            ->assertJsonPath('message', Studio::text('quote_success'));

        $lead = Lead::query()->sole();
        $this->assertSame('mobile_app', $lead->source);
        $this->assertSame('new', $lead->status);
        $this->assertSame('en', $lead->locale);
        $this->assertSame($service->id, $lead->service_id);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $recipient->id]);
    }

    public function test_lead_validation_rejects_bad_input_and_unavailable_services(): void
    {
        $draft = $this->service(['status' => 'draft']);

        $this->postJson('/api/v1/leads', ['name' => 'A', 'email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'message', 'consent']);
        $this->postJson('/api/v1/leads', $this->lead(['service_id' => $draft->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_id');
        $this->assertSame(0, Lead::count());
    }

    public function test_lead_honeypot_is_silently_ignored_and_submissions_are_rate_limited(): void
    {
        RateLimiter::clear('api-leads');

        $this->postJson('/api/v1/leads', $this->lead(['website' => 'https://spam.test']))->assertCreated();
        $this->assertSame(0, Lead::count());

        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/v1/leads', $this->lead())->assertCreated();
        }
        $this->postJson('/api/v1/leads', $this->lead())->assertTooManyRequests();
        $this->assertSame(4, Lead::count());
    }

    public function test_read_endpoints_send_cache_headers(): void
    {
        $this->getJson('/api/v1/config')
            ->assertHeader('Vary', 'Accept-Language')
            ->assertHeader('ETag');
    }
}
