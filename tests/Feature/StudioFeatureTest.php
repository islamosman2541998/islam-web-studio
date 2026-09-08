<?php

namespace Tests\Feature;

use App\Filament\Forms\Components\MediaLibraryPicker;
use App\Filament\Pages\MenuBuilder;
use App\Filament\Pages\StudioSettings;
use App\Filament\Resources\Assets\AssetResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Services\Pages\CreateRecord;
use App\Filament\Resources\Services\Pages\EditRecord;
use App\Filament\Resources\Services\Pages\ListRecords;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Slides\Pages\CreateRecord as CreateSlideRecord;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Widgets\ContentStatusChart;
use App\Filament\Widgets\LeadActivityChart;
use App\Filament\Widgets\StudioStats;
use App\Jobs\BuildExport;
use App\Livewire\BrowseContent;
use App\Livewire\QuoteForm;
use App\Models\Asset;
use App\Models\ExportRun;
use App\Models\Lead;
use App\Models\MenuItem;
use App\Models\MenuLocation;
use App\Models\MethodologyStep;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectMedia;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Models\Translation;
use App\Models\User;
use App\Support\FontRegistry;
use App\Support\MediaPicker;
use App\Support\MediaPipeline;
use App\Support\MenuResolver;
use App\Support\ModuleRegistry;
use App\Support\Preloader;
use App\Support\SettingsRegistry;
use App\Support\Studio;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StudioFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['studio.demo' => true]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function userWith(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function owner(): User
    {
        $permissions = ['dashboard.view', 'settings.update'];
        foreach (ModuleRegistry::all() as $key => $definition) {
            foreach (['view', 'create', 'update', 'delete', 'export'] as $action) {
                $permissions[] = $key.'.'.$action;
            }
        }
        $user = $this->userWith($permissions);
        $role = Role::findOrCreate('Owner', 'web');
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_typography_controls_and_module_icons_are_configured(): void
    {
        $dashboard = SettingsRegistry::groups()['dashboard'];

        $this->assertSame('600', $dashboard['small_text_weight'][3]);
        $this->assertSame('13', $dashboard['small_text_size'][3]);
        $this->assertSame('heroicon-o-photo', AssetResource::getNavigationIcon());
        $this->assertSame('heroicon-o-wrench-screwdriver', ServiceResource::getNavigationIcon());
        $this->assertArrayNotHasKey('icon', ModuleRegistry::get('services')['fields']);
        $this->assertSame('heroicon-o-computer-desktop', ProjectResource::getNavigationIcon());
        $this->assertSame('heroicon-o-newspaper', PostResource::getNavigationIcon());
        $this->assertSame(['IBM Plex Sans Arabic', 'Cairo', 'Tajawal'], array_keys(FontRegistry::arabicOptions()));
        $this->assertSame(['Cormorant Garamond', 'DM Sans', 'Inter', 'Poppins'], array_keys(FontRegistry::englishOptions()));
        $this->assertSame('IBM Plex Sans Arabic', FontRegistry::arabic('invalid-font'));
        $this->assertSame('Cormorant Garamond', FontRegistry::english('invalid-font'));

        Studio::put('dashboard.small_text_weight', '900', 'dashboard');
        Studio::put('dashboard.small_text_size', '99', 'dashboard');
        Studio::flush();

        $this->actingAs($this->owner())
            ->get('/admin')
            ->assertOk()
            ->assertSee('--studio-admin-small-weight:600', false)
            ->assertSee('--studio-admin-small-size:13px', false);

        Studio::put('design.arabic_font', 'Cairo', 'design');
        Studio::put('design.english_font', 'Inter', 'design');
        Studio::flush();

        $this->get('/ar')
            ->assertOk()
            ->assertSee("--arabic-font:'Cairo'", false)
            ->assertSee("--english-font:'Inter'", false);

        $this->get('/admin')
            ->assertOk()
            ->assertSee("--font-family: 'Cairo';", false);
    }

    public function test_login_overlay_and_card_appearance_are_configurable(): void
    {
        $login = SettingsRegistry::groups()['login'];

        $this->assertSame(79, $login['background_overlay_opacity'][3]);
        $this->assertSame('#FFFDF4', $login['card_background'][3]);
        $this->assertSame(100, $login['card_opacity'][3]);
        $this->assertSame('rgba(10,51,35,0.79)', Studio::rgba('#0A3323', 79, '#000000'));
        $this->assertSame('rgba(10,51,35,0)', Studio::rgba('#0A3323', -20, '#000000'));
        $this->assertSame('rgba(255,253,244,1)', Studio::rgba('#FFFDF4', 200, '#000000'));

        Studio::put('login.background', '#123456', 'login');
        Studio::put('login.background_overlay_opacity', 0, 'login');
        Studio::put('login.card_background', '#ABCDEF', 'login');
        Studio::put('login.card_opacity', 55, 'login');
        Studio::flush();

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('--studio-login-bg: #123456;', false)
            ->assertSee('--studio-login-overlay: rgba(18,52,86,0);', false)
            ->assertSee('--studio-login-card: rgba(171,205,239,0.55);', false);
    }

    public function test_every_login_card_text_group_has_configurable_color_and_size(): void
    {
        $controls = [
            'title' => ['#112233', 31, 'title'],
            'description' => ['#223344', 18, 'description'],
            'label' => ['#334455', 16, 'label'],
            'input' => ['#445566', 17, 'input'],
            'button' => ['#556677', 15, 'button-text'],
            'link' => ['#667788', 14, 'link'],
            'footer' => ['#778899', 12, 'footer'],
        ];
        $login = SettingsRegistry::groups()['login'];

        foreach ($controls as $area => [$color, $size]) {
            $this->assertArrayHasKey($area.'_text_color', $login);
            $this->assertArrayHasKey($area.'_text_size', $login);
            Studio::put('login.'.$area.'_text_color', $color, 'login');
            Studio::put('login.'.$area.'_text_size', $size, 'login');
        }
        Studio::flush();

        $response = $this->get('/admin/login')->assertOk();
        foreach ($controls as [$color, $size, $cssArea]) {
            $response
                ->assertSee('--studio-login-'.$cssArea.'-color: '.$color.';', false)
                ->assertSee('--studio-login-'.$cssArea.'-size: '.$size.'px;', false);
        }
    }

    public function test_dashboard_uses_live_metrics_and_professional_charts_without_demo_copy(): void
    {
        $owner = $this->owner();
        Service::factory()->create(['status' => 'published']);
        Service::factory()->create(['status' => 'draft']);
        Project::factory()->create(['status' => 'published']);
        Post::factory()->create(['status' => 'draft']);
        Lead::factory()->create(['status' => 'new', 'created_at' => today()]);
        Lead::factory()->create(['status' => 'contacted', 'created_at' => today()->subDays(3)]);

        $this->actingAs($owner)
            ->get('/admin')
            ->assertOk()
            ->assertSee(Studio::text('dashboard_title'))
            ->assertSee(Studio::text('dashboard_subheading'))
            ->assertDontSee(Studio::text('demo_notice'));

        Livewire::actingAs($owner)
            ->test(StudioStats::class)
            ->assertSee(Studio::text('dashboard_key_metrics'))
            ->assertSee(Studio::text('dashboard_new_waiting', ['count' => 1]));

        Livewire::actingAs($owner)
            ->test(LeadActivityChart::class)
            ->assertSee(Studio::text('dashboard_lead_activity'));

        Livewire::actingAs($owner)
            ->test(ContentStatusChart::class)
            ->assertSee(Studio::text('dashboard_content_status'));

        $activityChart = app(LeadActivityChart::class);
        $activityData = (fn (): array => $this->getData())->bindTo($activityChart, $activityChart)();
        $this->assertSame(2, array_sum($activityData['datasets'][0]['data']));

        $contentChart = app(ContentStatusChart::class);
        $contentData = (fn (): array => $this->getData())->bindTo($contentChart, $contentChart)();
        $this->assertSame([1, 1, 0], $contentData['datasets'][0]['data']);
        $this->assertSame([1, 0, 1], $contentData['datasets'][1]['data']);
    }

    public function test_public_routes_render_in_both_languages_with_seo(): void
    {
        Page::factory()->create(['template' => 'about', 'status' => 'published']);
        $service = Service::factory()->create(['status' => 'published', 'service_category_id' => ServiceCategory::factory()->create(['status' => 'published'])->id]);
        $project = Project::factory()->create(['status' => 'published', 'project_category_id' => ProjectCategory::factory()->create(['status' => 'published'])->id]);
        $post = Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay(), 'post_category_id' => PostCategory::factory()->create(['status' => 'published'])->id, 'author_id' => User::factory()->create()->id]);
        foreach (['ar', 'en'] as $locale) {
            foreach (['', '/services', '/work', '/journal', '/about', '/process', '/testimonials', '/contact', '/services/'.$service->text('slug', $locale), '/work/'.$project->text('slug', $locale), '/journal/'.$post->text('slug', $locale)] as $path) {
                $this->get('/'.$locale.$path)->assertOk()->assertSee('lang="'.$locale.'"', false)->assertSee('rel="canonical"', false);
            }
        }
        $this->get('/')->assertRedirect();
        $this->get('/sitemap.xml')->assertOk();
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
    }

    public function test_google_metadata_uses_managed_home_settings_and_complete_structured_data(): void
    {
        config(['studio.demo' => false]);
        Storage::fake('public');
        $logo = Asset::factory()->create(['name' => ['ar' => 'لوجو البحث', 'en' => 'Search logo']]);
        $logo->addMedia(UploadedFile::fake()->image('search-logo.png', 600, 600))->toMediaCollection('original', 'public');
        Page::factory()->create([
            'template' => 'home',
            'status' => 'published',
            'title' => ['ar' => 'عنوان الصفحة المرئي', 'en' => 'Visible page heading'],
            'meta_title' => ['ar' => '', 'en' => ''],
            'meta_description' => ['ar' => '', 'en' => ''],
        ]);
        Studio::put('seo.title', ['ar' => 'حلول رقمية احترافية | إسلام ويب ستوديو', 'en' => 'Professional digital solutions | Islam Web Studio'], 'seo');
        Studio::put('seo.description', ['ar' => 'نطور مواقع ومتاجر وأنظمة تساعد مشروعك على النمو.', 'en' => 'We build websites, commerce and systems that help businesses grow.'], 'seo');
        Studio::put('seo.logo', $logo->id, 'seo');
        Studio::flush();

        $this->get('/ar')
            ->assertOk()
            ->assertSee('<title>حلول رقمية احترافية | إسلام ويب ستوديو</title>', false)
            ->assertSee('content="نطور مواقع ومتاجر وأنظمة تساعد مشروعك على النمو."', false)
            ->assertSee('max-image-preview:large', false)
            ->assertSee('property="og:site_name"', false)
            ->assertSee('name="twitter:title"', false)
            ->assertSee('rel="apple-touch-icon"', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"@type":"WebPage"', false)
            ->assertSee('"logo":', false)
            ->assertSee('"sameAs":', false);
    }

    public function test_preloader_design_is_fully_managed_and_rendered_for_loading_and_navigation(): void
    {
        $preloader = SettingsRegistry::groups()['preloader'];
        foreach (['background_type', 'background_color', 'background_opacity', 'background_image', 'background_image_opacity', 'background_fit', 'panel_enabled', 'panel_color', 'panel_opacity', 'panel_radius', 'logo_width', 'show_progress', 'progress_color', 'minimum_duration'] as $key) {
            $this->assertArrayHasKey($key, $preloader);
        }

        Studio::put('preloader.background_color', '#123456', 'preloader');
        Studio::put('preloader.background_opacity', 55, 'preloader');
        Studio::put('preloader.panel_enabled', true, 'preloader');
        Studio::put('preloader.panel_color', '#010203', 'preloader');
        Studio::put('preloader.panel_opacity', 70, 'preloader');
        Studio::put('preloader.panel_radius', 32, 'preloader');
        Studio::put('preloader.logo_width', 120, 'preloader');
        Studio::put('preloader.show_text', true, 'preloader');
        Studio::put('preloader.text', ['ar' => 'جاري تجهيز الموقع', 'en' => 'Preparing the website'], 'preloader');
        Studio::put('preloader.animation', 'float', 'preloader');
        Studio::put('preloader.minimum_duration', 975, 'preloader');
        Studio::flush();

        $resolved = Preloader::make();
        $this->assertSame('rgba(18,52,86,0.55)', $resolved['background_color']);
        $this->assertSame('rgba(1,2,3,0.7)', $resolved['panel_color']);
        $this->assertSame(120, $resolved['logo_width']);

        $this->get('/ar')
            ->assertOk()
            ->assertSee('id="preloader"', false)
            ->assertSee('class="studio-loader page-transition"', false)
            ->assertSee('data-animation="float"', false)
            ->assertSee('--loader-background-color:rgba(18,52,86,0.55)', false)
            ->assertSee('--loader-panel:rgba(1,2,3,0.7)', false)
            ->assertSee('--loader-radius:32px', false)
            ->assertSee('--loader-logo-width:120px', false)
            ->assertSee('data-loader-delay="975"', false)
            ->assertSee('جاري تجهيز الموقع');
    }

    public function test_project_detail_combines_main_media_and_gallery_without_public_demo_labels(): void
    {
        $main = Asset::factory()->create([
            'name' => ['ar' => 'الصورة الرئيسية', 'en' => 'Main image'],
            'alt' => ['ar' => 'الصورة الرئيسية', 'en' => 'Main image'],
        ]);
        $galleryImage = Asset::factory()->create([
            'name' => ['ar' => 'صورة التفاصيل', 'en' => 'Detail image'],
            'alt' => ['ar' => 'صورة التفاصيل', 'en' => 'Detail image'],
        ]);
        $category = ProjectCategory::factory()->create([
            'name' => ['ar' => 'أنظمة', 'en' => 'Systems'],
            'status' => 'published',
        ]);
        $project = Project::factory()->create([
            'title' => ['ar' => 'نظام إدارة', 'en' => 'Management system'],
            'status' => 'published',
            'project_category_id' => $category->id,
            'main_media_id' => $main->id,
            'live_url' => 'https://example.com/project',
        ]);
        ProjectMedia::create([
            'project_id' => $project->id,
            'media_id' => $galleryImage->id,
            'type' => 'image',
            'caption' => ['ar' => 'تفاصيل النظام', 'en' => 'System details'],
            'sort_order' => 0,
        ]);
        Project::factory()->create([
            'title' => ['ar' => 'عمل مرتبط', 'en' => 'Related work'],
            'status' => 'published',
            'project_category_id' => $category->id,
            'main_media_id' => $galleryImage->id,
        ]);
        Project::factory()->count(3)->create([
            'status' => 'published',
            'project_category_id' => $category->id,
            'main_media_id' => $galleryImage->id,
        ]);

        $response = $this->get('/ar/work/'.$project->text('slug', 'ar'))
            ->assertOk()
            ->assertSee('project-showcase', false)
            ->assertSee('project-gallery--hero', false)
            ->assertSee('project-gallery-main', false)
            ->assertSee('project-showcase-link', false)
            ->assertSee('related-projects', false)
            ->assertSee('related-projects-swiper', false)
            ->assertSee('data-swiper-kind="related"', false)
            ->assertSee('data-autoplay="1"', false)
            ->assertSee('data-drag="1"', false)
            ->assertSee('data-loop="1"', false)
            ->assertDontSee('gallery-section', false)
            ->assertDontSee('inline-demo', false)
            ->assertDontSee(Studio::text('demo_notice'));

        $this->assertSame(2, substr_count($response->getContent(), 'data-slide-to="'));
    }

    public function test_public_archive_filters_refresh_results_and_page_labels_follow_the_locale(): void
    {
        $development = ServiceCategory::factory()->create([
            'name' => ['ar' => 'التطوير', 'en' => 'Development'],
            'status' => 'published',
        ]);
        $design = ServiceCategory::factory()->create([
            'name' => ['ar' => 'التصميم', 'en' => 'Design'],
            'status' => 'published',
        ]);
        Service::factory()->create([
            'name' => ['ar' => 'خدمة برمجية', 'en' => 'Development service'],
            'service_category_id' => $development->id,
            'status' => 'published',
        ]);
        Service::factory()->create([
            'name' => ['ar' => 'خدمة تصميم', 'en' => 'Design service'],
            'service_category_id' => $design->id,
            'status' => 'published',
        ]);

        Livewire::test(BrowseContent::class, ['module' => 'services'])
            ->assertSee('خدمة برمجية')
            ->assertSee('خدمة تصميم')
            ->set('category', (string) $development->id)
            ->assertSee('خدمة برمجية')
            ->assertDontSee('خدمة تصميم')
            ->set('category', (string) $design->id)
            ->assertDontSee('خدمة برمجية')
            ->assertSee('خدمة تصميم');

        $this->get('/ar/services')
            ->assertOk()
            ->assertSee('إسلام ويب ستوديو / الخدمات')
            ->assertDontSee('ISLAM WEB STUDIO / SERVICES');
        $this->get('/en/services')
            ->assertOk()
            ->assertSee('Islam Web Studio / Services');
        $this->get('/ar/process')->assertSee('إسلام ويب ستوديو / منهجيتنا');
        $this->get('/ar/testimonials')->assertSee('إسلام ويب ستوديو / آراء العملاء');
        $this->get('/ar/contact')->assertSee('إسلام ويب ستوديو / تواصل معنا');
    }

    public function test_dynamic_pages_use_managed_story_media_and_gallery_without_founder_or_cta_sections(): void
    {
        $hero = Asset::factory()->create(['kind' => 'image', 'visibility' => 'public', 'is_active' => true]);
        $galleryImage = Asset::factory()->create(['kind' => 'image', 'visibility' => 'public', 'is_active' => true]);
        $galleryVideo = Asset::factory()->create(['kind' => 'video', 'visibility' => 'public', 'is_active' => true]);
        $galleryImageTwo = Asset::factory()->create(['kind' => 'image', 'visibility' => 'public', 'is_active' => true]);
        $galleryFile = Asset::factory()->create(['kind' => 'file', 'visibility' => 'public', 'is_active' => true]);

        $page = Page::factory()->create([
            'template' => 'about',
            'status' => 'published',
            'hero_media_id' => $hero->id,
            'title' => ['ar' => 'عنوان من نحن المُدار', 'en' => 'Managed about title'],
            'excerpt' => ['ar' => 'الوصف المختصر المُدار', 'en' => 'Managed short description'],
            'content' => ['ar' => '<h2>الوصف الكبير المُدار</h2><p>تفاصيل الاستوديو الكاملة.</p>', 'en' => '<h2>Managed long description</h2><p>Full studio details.</p>'],
        ]);

        $page->gallery()->createMany([
            ['media_id' => $galleryImage->id, 'kind' => 'gallery_image', 'caption' => ['ar' => 'صورة من الجاليري', 'en' => 'Gallery image'], 'sort_order' => 0],
            ['media_id' => $galleryVideo->id, 'kind' => 'video', 'caption' => ['ar' => 'فيديو من الجاليري', 'en' => 'Gallery video'], 'sort_order' => 1],
            ['media_id' => $galleryImageTwo->id, 'kind' => 'gallery_image', 'caption' => ['ar' => 'صورة ثانية', 'en' => 'Second gallery image'], 'sort_order' => 2],
            ['media_id' => $galleryFile->id, 'kind' => 'file', 'caption' => ['ar' => 'ملف للتحميل', 'en' => 'Downloadable file'], 'sort_order' => 3],
        ]);

        $this->get('/ar/about')
            ->assertOk()
            ->assertSee('عنوان من نحن المُدار')
            ->assertSee('الوصف المختصر المُدار')
            ->assertSee('class="page-intro dynamic-page-intro shell"', false)
            ->assertSee('dynamic-page-story', false)
            ->assertSee('class="dynamic-page-story-media"', false)
            ->assertSee('الوصف الكبير المُدار')
            ->assertSee('class="page-gallery"', false)
            ->assertSee('<video', false)
            ->assertSee('data-studio-video', false)
            ->assertSee('data-video-progress', false)
            ->assertSee('data-reveal="side"', false)
            ->assertSee('data-reveal="up"', false)
            ->assertSee('data-reveal="down"', false)
            ->assertDontSee('studio-video__media" controls', false)
            ->assertSee('ملف للتحميل')
            ->assertDontSee('founder-note')
            ->assertDontSee('cta-band')
            ->assertDontSee('home-contact');

        $standardPage = Page::factory()->create([
            'template' => 'standard',
            'status' => 'published',
            'hero_media_id' => $galleryVideo->id,
        ]);

        $this->get('/ar/'.$standardPage->text('slug', 'ar'))
            ->assertOk()
            ->assertSee('dynamic-page-story', false)
            ->assertSee('data-studio-video', false)
            ->assertDontSee('class="detail-hero"', false);

        $homeSettings = SettingsRegistry::groups()['home'];
        $this->assertArrayNotHasKey('cta_title', $homeSettings);
        $this->assertArrayNotHasKey('cta_text', $homeSettings);
    }

    public function test_home_service_cards_render_their_images_without_icons(): void
    {
        $image = Asset::factory()->create();
        Service::factory()->create([
            'status' => 'published',
            'is_featured' => true,
            'image_media_id' => $image->id,
        ]);

        $this->get('/ar')
            ->assertOk()
            ->assertSee('class="services-heading"', false)
            ->assertSee('class="service-card-media"', false)
            ->assertSee('<picture', false)
            ->assertDontSee('service-symbol')
            ->assertDontSee('service-card-index');
    }

    public function test_section_and_page_headings_are_managed_by_interface_translations(): void
    {
        $copy = [
            'what_we_do' => 'نص خدمات الرئيسية',
            'services_title' => 'عنوان الخدمات المشترك',
            'services_intro' => 'وصف الخدمات المشترك',
            'route_services.index' => 'نص صفحة الخدمات',
            'selected_work' => 'نص أعمال الرئيسية',
            'projects_title' => 'عنوان الأعمال المشترك',
            'projects_intro' => 'وصف الأعمال المشترك',
            'route_projects.index' => 'نص صفحة الأعمال',
            'our_process' => 'نص المنهجية في الرئيسية',
            'methodology_title' => 'عنوان المنهجية المشترك',
            'methodology_intro' => 'وصف المنهجية المشترك',
            'route_methodology' => 'نص صفحة المنهجية',
            'client_words' => 'نص الآراء في الرئيسية',
            'testimonials_title' => 'عنوان الآراء المشترك',
            'testimonials_intro' => 'وصف الآراء المشترك',
            'route_testimonials' => 'نص صفحة الآراء',
            'insights' => 'نص المدونة في الرئيسية',
            'posts_title' => 'عنوان المدونة المشترك',
            'posts_intro' => 'وصف المدونة المشترك',
            'route_posts.index' => 'نص صفحة المدونة',
            'contact_title' => 'عنوان التواصل',
            'contact_intro' => 'وصف التواصل',
            'route_contact' => 'نص صفحة التواصل',
        ];

        foreach ($copy as $key => $value) {
            Translation::updateOrCreate(
                ['key' => $key],
                ['value' => ['ar' => $value, 'en' => 'English '.$key], 'is_active' => true],
            );
        }
        Studio::flush();

        $this->get('/ar')->assertOk()
            ->assertSee('نص خدمات الرئيسية')->assertSee('عنوان الخدمات المشترك')->assertSee('وصف الخدمات المشترك')
            ->assertSee('نص أعمال الرئيسية')->assertSee('عنوان الأعمال المشترك')->assertSee('وصف الأعمال المشترك')
            ->assertSee('نص المنهجية في الرئيسية')->assertSee('عنوان المنهجية المشترك')->assertSee('وصف المنهجية المشترك')
            ->assertSee('نص الآراء في الرئيسية')->assertSee('عنوان الآراء المشترك')->assertSee('وصف الآراء المشترك')
            ->assertSee('نص المدونة في الرئيسية')->assertSee('عنوان المدونة المشترك')->assertSee('وصف المدونة المشترك');

        foreach ([
            '/ar/services' => ['نص صفحة الخدمات', 'عنوان الخدمات المشترك', 'وصف الخدمات المشترك'],
            '/ar/work' => ['نص صفحة الأعمال', 'عنوان الأعمال المشترك', 'وصف الأعمال المشترك'],
            '/ar/journal' => ['نص صفحة المدونة', 'عنوان المدونة المشترك', 'وصف المدونة المشترك'],
            '/ar/process' => ['نص صفحة المنهجية', 'عنوان المنهجية المشترك', 'وصف المنهجية المشترك'],
            '/ar/testimonials' => ['نص صفحة الآراء', 'عنوان الآراء المشترك', 'وصف الآراء المشترك'],
            '/ar/contact' => ['نص صفحة التواصل', 'عنوان التواصل', 'وصف التواصل'],
        ] as $url => [$label, $title, $description]) {
            $this->get($url)->assertOk()->assertSee($label)->assertSee($title)->assertSee($description);
        }

        $homeSettings = SettingsRegistry::groups()['home'];
        $this->assertArrayNotHasKey('page_headers', SettingsRegistry::groups());
        foreach (['services', 'projects', 'process', 'testimonials', 'posts'] as $section) {
            $this->assertArrayNotHasKey($section.'_label', $homeSettings);
            $this->assertArrayNotHasKey($section.'_title', $homeSettings);
            $this->assertArrayNotHasKey($section.'_description', $homeSettings);
        }
    }

    public function test_drafts_future_posts_and_unapproved_reviews_are_not_public(): void
    {
        $draft = Page::factory()->create(['status' => 'draft']);
        $future = Post::factory()->create(['status' => 'published', 'published_at' => now()->addDay()]);
        $this->get('/en/'.$draft->text('slug', 'en'))->assertNotFound();
        $this->get('/en/journal/'.$future->text('slug', 'en'))->assertNotFound();
        Testimonial::factory()->create(['quote' => ['en' => 'Unapproved secret review', 'ar' => 'تقييم غير معتمد'], 'is_approved' => false]);
        $this->get('/en/testimonials')->assertDontSee('Unapproved secret review');
    }

    public function test_dynamic_menu_selection_cache_invalidation_and_translated_urls(): void
    {
        app()->setLocale('en');
        $menu = MenuLocation::factory()->create(['key' => 'navbar']);
        $visible = Service::factory()->create(['status' => 'published', 'slug' => ['ar' => 'خدمة-مميزة', 'en' => 'chosen-service']]);
        $draft = Service::factory()->create(['status' => 'draft']);
        MenuItem::factory()->create(['menu_location_id' => $menu->id, 'type' => 'dynamic_group', 'dynamic_source' => 'services', 'dynamic_mode' => 'selected', 'dynamic_items' => [$visible->id, $draft->id], 'visibility' => 'all']);
        $this->assertCount(1, MenuResolver::forLocation('navbar')[0]['children']);
        $this->assertStringContainsString('/en/services/chosen-service', MenuResolver::forLocation('navbar')[0]['children'][0]['url']);
        $visible->update(['is_active' => false]);
        $this->assertSame([], MenuResolver::forLocation('navbar'));
    }

    public function test_admin_resources_and_forms_render_for_authorized_owner(): void
    {
        $this->actingAs($this->owner());
        foreach (ModuleRegistry::all() as $key => $definition) {
            if ($definition['child'] ?? false) {
                continue;
            }$path = '/admin/'.str_replace('_', '-', $key);
            $this->get($path)->assertOk();
            if (! ($definition['readonly'] ?? false)) {
                $this->get($path.'/create')->assertOk();
            }
        }
        foreach (['/admin', '/admin/studio-settings', '/admin/menu-builder', '/admin/lead-pipeline', '/admin/users', '/admin/roles'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_localized_admin_urls_redirect_to_the_dashboard(): void
    {
        $this->get('/ar/admin')->assertRedirect('/admin');
        $this->get('/en/admin/services')->assertRedirect('/admin/services');
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_viewer_cannot_edit_or_change_inline_content(): void
    {
        $viewer = $this->userWith(['dashboard.view', 'services.view']);
        $service = Service::factory()->create(['is_featured' => false]);
        $this->actingAs($viewer)->get('/admin/services/'.$service->id.'/edit')->assertForbidden();
        $this->get('/admin/leads')->assertForbidden();
        Livewire::actingAs($viewer)->test(ListRecords::class)->call('updateTableColumnState', 'is_featured', (string) $service->id, true);
        $this->assertFalse($service->refresh()->is_featured);
    }

    public function test_owner_can_toggle_featured_without_navigation_and_filter_records(): void
    {
        $owner = $this->owner();
        $first = Service::factory()->create(['name' => ['ar' => 'متجر ألف', 'en' => 'Alpha store'], 'is_featured' => false]);
        $second = Service::factory()->create(['name' => ['ar' => 'موقع بيتا', 'en' => 'Beta website'], 'is_featured' => true]);
        $component = Livewire::actingAs($owner)->test(ListRecords::class)->call('updateTableColumnState', 'is_featured', (string) $first->id, true);
        $this->assertTrue($first->refresh()->is_featured);
        $component->searchTable('Alpha')->assertCanSeeTableRecords([$first])->assertCanNotSeeTableRecords([$second]);
    }

    public function test_quote_form_validates_and_saves_once_with_server_controlled_status(): void
    {
        $service = Service::factory()->create(['status' => 'published']);
        Livewire::test(QuoteForm::class)->call('submit')->assertHasErrors(['name', 'email', 'phone', 'message', 'consent']);
        $form = Livewire::test(QuoteForm::class)->set('name', 'Test visitor')->set('email', 'visitor@example.test')->set('phone', '+201000000000')->set('service_id', (string) $service->id)->set('message', 'A real test message about a new website project.')->set('consent', true)->call('submit')->assertHasNoErrors()->assertSet('sent', true);
        $form->call('submit');
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseHas('leads', ['email' => 'visitor@example.test', 'status' => 'new', 'source' => 'form']);
    }

    public function test_owner_can_update_their_password_and_receives_a_success_notification(): void
    {
        $owner = $this->owner();
        $ownerRoleId = (string) $owner->roles()->firstOrFail()->id;

        Livewire::actingAs($owner)
            ->test(EditUser::class, ['record' => $owner->id])
            ->fillForm([
                'name' => $owner->name,
                'email' => $owner->email,
                'password' => 'A-new-secure-password-2026',
                'roles' => [$ownerRoleId],
                'locale' => 'ar',
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified(Studio::text('saved'));

        $this->assertTrue(Hash::check('A-new-secure-password-2026', $owner->refresh()->password));
    }

    public function test_quote_form_sends_dynamic_toast_and_database_notification(): void
    {
        $recipient = $this->userWith(['leads.view']);

        Livewire::test(QuoteForm::class)
            ->set('name', 'Notification visitor')
            ->set('email', 'notify@example.test')
            ->set('phone', '+201000000001')
            ->set('message', 'A complete project request that should notify the studio team.')
            ->set('consent', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertDispatched('studio-toast', type: 'success');

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $recipient->id,
        ]);
        $this->assertSame('filament', $recipient->notifications()->firstOrFail()->data['format']);
    }

    public function test_honeypot_and_draft_service_do_not_create_leads(): void
    {
        Livewire::test(QuoteForm::class)->set('website', 'bot')->call('submit');
        $this->assertDatabaseCount('leads', 0);
        $draft = Service::factory()->create(['status' => 'draft']);
        Livewire::test(QuoteForm::class)->set('name', 'Test visitor')->set('email', 'visitor@example.test')->set('phone', '+201000000000')->set('service_id', (string) $draft->id)->set('message', 'A test request for a service that should be unavailable.')->set('consent', true)->call('submit')->assertHasErrors('service_id');
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_private_media_requires_authentication_and_permission(): void
    {
        $asset = Asset::factory()->create(['visibility' => 'private']);
        $this->get('/private-assets/'.$asset->id)->assertRedirect();
        $viewer = $this->userWith(['dashboard.view']);
        $this->actingAs($viewer)->get('/private-assets/'.$asset->id)->assertForbidden();
    }

    public function test_excel_export_is_scoped_private_and_formula_safe(): void
    {
        Storage::fake('local');
        $owner = $this->owner();
        $lead = Lead::factory()->create(['name' => '=HYPERLINK("https://example.test")']);
        $excluded = Lead::factory()->create(['name' => 'Excluded lead']);
        $run = ExportRun::create(['user_id' => $owner->id, 'module' => 'leads', 'ids' => [$lead->id], 'columns' => ['name', 'email', 'status'], 'locale' => 'both', 'status' => 'queued']);
        BuildExport::dispatchSync($run->id);
        $run->refresh();
        $this->assertSame('completed', $run->status);
        Storage::disk('local')->assertExists($run->file_path);
        $book = IOFactory::load(Storage::disk('local')->path($run->file_path));
        $sheet = $book->getActiveSheet();
        $this->assertSame(2, $sheet->getHighestRow());
        $this->assertSame('s', $sheet->getCell('B2')->getDataType());
        $this->assertSame($lead->name, $sheet->getCell('B2')->getValue());
        $this->actingAs($owner)->get('/exports/'.$run->id.'/download')->assertOk();
        $other = $this->userWith(['dashboard.view', 'leads.export']);
        $this->actingAs($other)->get('/exports/'.$run->id.'/download')->assertForbidden();
    }

    public function test_rich_text_sanitizer_removes_scripts_and_unsafe_links(): void
    {
        $html = Studio::cleanHtml('<p>Safe text</p><script>alert(1)</script><a href="javascript:alert(1)">Link</a><img src=x onerror=alert(1)>');
        $this->assertStringContainsString('Safe text', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('onerror', $html);
    }

    public function test_bilingual_service_create_edit_and_child_features_persist(): void
    {
        $owner = $this->owner();
        Livewire::actingAs($owner)->test(CreateRecord::class)
            ->fillForm(['name' => ['ar' => 'خدمة اختبار', 'en' => 'QA service'], 'slug' => ['ar' => 'خدمة-اختبار', 'en' => 'qa-service'], 'short_description' => ['ar' => 'وصف الخدمة', 'en' => 'Service summary'], 'long_description' => ['ar' => '<p>وصف تفصيلي</p>', 'en' => '<p>Detailed description</p>'], 'service_category_id' => ServiceCategory::factory()->create()->id, 'status' => 'published', 'is_active' => true, 'is_featured' => false, 'sort_order' => 0, 'pricing_type' => 'on_request', 'seo_index' => true])
            ->call('create')->assertHasNoFormErrors();
        $service = Service::where('slug->en', 'qa-service')->firstOrFail();
        $this->assertSame('خدمة اختبار', $service->text('name', 'ar'));
        $service->features()->create(['text' => ['ar' => 'ميزة قديمة', 'en' => 'Old feature'], 'sort_order' => 0]);
        $edit = Livewire::actingAs($owner)->test(EditRecord::class, ['record' => $service->id]);
        $features = $edit->get('data.features');
        $key = array_key_first($features);
        $features[$key]['text'] = ['ar' => 'ميزة جديدة', 'en' => 'Updated feature'];
        $edit->fillForm(['name' => ['ar' => 'خدمة معدلة', 'en' => 'Edited service'], 'features' => $features])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Edited service', $service->refresh()->text('name', 'en'));
        $this->assertSame('ميزة جديدة', $service->features()->first()->text('text', 'ar'));
    }

    public function test_filtered_export_uses_search_and_inclusive_dates(): void
    {
        Queue::fake();
        $owner = $this->owner();
        $selected = Lead::factory()->create(['name' => 'Selected request', 'created_at' => '2026-08-31 22:30:00']);
        Lead::factory()->create(['name' => 'Selected old request', 'created_at' => '2026-07-31 22:30:00']);
        Lead::factory()->create(['name' => 'Other request', 'created_at' => '2026-08-15 12:00:00']);
        Livewire::actingAs($owner)->test(\App\Filament\Resources\Leads\Pages\ListRecords::class)
            ->searchTable('Selected')->filterTable('created_range', ['from' => '2026-08-01', 'until' => '2026-08-31'])
            ->assertCanSeeTableRecords([$selected])->callAction(TestAction::make('export')->table(), ['locale' => 'both'])->assertHasNoActionErrors();
        $run = ExportRun::firstOrFail();
        $this->assertSame([$selected->id], $run->ids);
        $this->assertSame(1, $run->row_count);
        Queue::assertPushed(BuildExport::class);
    }

    public function test_menu_tree_rejects_cycles_and_does_not_partially_save(): void
    {
        $owner = $this->owner();
        $menu = MenuLocation::factory()->create();
        $a = MenuItem::factory()->create(['menu_location_id' => $menu->id, 'parent_id' => null, 'sort_order' => 0]);
        $b = MenuItem::factory()->create(['menu_location_id' => $menu->id, 'parent_id' => null, 'sort_order' => 1]);
        Livewire::actingAs($owner)->test(MenuBuilder::class)->set('location', $menu->id)
            ->call('saveTree', [['id' => $a->id, 'parent_id' => $b->id, 'sort_order' => 1], ['id' => $b->id, 'parent_id' => $a->id, 'sort_order' => 0]])->assertStatus(422);
        $this->assertNull($a->refresh()->parent_id);
        $this->assertNull($b->refresh()->parent_id);
        Livewire::actingAs($owner)->test(MenuBuilder::class)->set('location', $menu->id)
            ->call('saveTree', [['id' => $a->id, 'parent_id' => null, 'sort_order' => 0], ['id' => $b->id, 'parent_id' => $a->id, 'sort_order' => 0]])->assertHasNoErrors();
        $this->assertSame($a->id, $b->refresh()->parent_id);
    }

    public function test_custom_menu_location_is_invalidated_when_content_changes(): void
    {
        app()->setLocale('en');
        $location = MenuLocation::factory()->create(['key' => 'landing-links']);
        $page = Page::factory()->create(['status' => 'published', 'template' => 'default']);
        MenuItem::factory()->create(['menu_location_id' => $location->id, 'type' => 'page', 'page_id' => $page->id, 'visibility' => 'all']);
        $this->assertCount(1, MenuResolver::forLocation('landing-links'));
        $page->update(['status' => 'draft']);
        $this->assertSame([], MenuResolver::forLocation('landing-links'));
    }

    public function test_media_pipeline_creates_responsive_images_in_private_storage(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Queue::fake();
        $source = UploadedFile::fake()->image('test.jpg', 480, 320)->storeAs('incoming', 'safe-test.jpg', 'local');
        $asset = Asset::factory()->create(['visibility' => 'private', 'upload_path' => $source]);
        $this->assertNull($asset->upload_path);
        $this->assertNotNull($asset->original());
        $this->assertTrue($asset->metadata['processed']);
        $this->assertFalse($asset->metadata['optimized']);
        app(MediaPipeline::class)->process($asset);
        $asset->refresh();
        $this->assertSame(480, $asset->metadata['width']);
        $this->assertSame(320, $asset->metadata['height']);
        $this->assertNotEmpty($asset->metadata['webp']);
        $this->assertNotEmpty($asset->metadata['avif']);
        $this->assertStringStartsWith('data:image/webp;base64,', $asset->metadata['lqip']);
        foreach ($asset->metadata['webp'] as $path) {
            Storage::disk('local')->assertExists($path);
            Storage::disk('public')->assertMissing($path);
        }
        Storage::disk('local')->assertMissing($source);
        $this->assertNull($asset->upload_path);
    }

    public function test_visual_media_picker_renders_previews_and_adds_upload_to_library(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Queue::fake();

        $image = Asset::factory()->create(['kind' => 'image']);
        $image->addMedia(UploadedFile::fake()->image('preview.jpg', 320, 220))->toMediaCollection('original', 'public');
        $video = Asset::factory()->create(['kind' => 'video']);
        $video->addMedia(UploadedFile::fake()->create('preview.mp4', 120, 'video/mp4'))->toMediaCollection('original', 'public');

        $this->assertStringContainsString('<img', MediaPicker::optionHtml($image));
        $this->assertStringContainsString('<video', MediaPicker::optionHtml($video));

        $picker = MediaPicker::make('photo_media_id', ['image']);
        $this->assertInstanceOf(MediaLibraryPicker::class, $picker);
        $this->assertSame(['image'], $picker->getMediaTypes());
        $this->assertSame([$image->id], $picker->getMediaAssets()->pluck('id')->all());

        Livewire::actingAs($this->owner())
            ->test(CreateSlideRecord::class)
            ->assertSee('iws-media-picker__control', false)
            ->assertSee('iws-media-library__grid', false)
            ->assertSee(Studio::text('choose_from_media_library'))
            ->assertSee(Studio::text('preview'));

        $path = UploadedFile::fake()->image('new-library-image.jpg', 480, 320)->store('incoming', 'local');
        $created = MediaPicker::createAsset([
            'upload_path' => $path,
            'name_ar' => 'صورة من الاختيار',
            'name_en' => 'Picker image',
            'alt_ar' => 'وصف الصورة',
            'alt_en' => 'Image description',
        ], ['image']);

        $this->assertSame('public', $created->visibility);
        $this->assertSame('image', $created->kind);
        $this->assertNull($created->upload_path);
        $this->assertTrue($created->metadata['processed']);
        $this->assertFalse($created->metadata['optimized']);
        $this->assertNotNull($created->original());
        Storage::disk('local')->assertMissing($path);
        app()->setLocale('en');
        $this->assertStringContainsString('Picker image', MediaPicker::optionHtml($created));
    }

    public function test_media_picker_upload_action_adds_asset_to_grid_and_selects_it(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Queue::fake();

        $slider = Slider::factory()->create(['name' => 'Upload target']);

        $component = Livewire::actingAs($this->owner())
            ->test(CreateSlideRecord::class)
            ->fillForm(['slider_id' => $slider->id])
            ->assertDontSee('صورة مرفوعة من المكتبة')
            ->callAction(
                TestAction::make('uploadMedia')->schemaComponent('desktop_media_id'),
                [
                    'upload_path' => UploadedFile::fake()->image('inline-upload.jpg', 480, 320),
                    'name_ar' => 'صورة مرفوعة من المكتبة',
                    'name_en' => 'Inline upload',
                ],
            )
            ->assertHasNoActionErrors();

        $created = Asset::query()->where('name->ar', 'صورة مرفوعة من المكتبة')->sole();

        // The new asset shows in the library grid immediately, without a page refresh...
        $component->assertSee('صورة مرفوعة من المكتبة')
            ->assertSee('data-asset-id="'.$created->getKey().'"', false)
            // ...and is pre-selected as the field value.
            ->assertFormSet(['desktop_media_id' => $created->getKey()]);
    }

    public function test_only_one_home_hero_renders_and_its_video_is_deferred(): void
    {
        $page = Page::factory()->create(['status' => 'published', 'template' => 'default']);
        $oldSlider = Slider::factory()->create(['name' => 'Old hero', 'location' => 'page:'.$page->id]);
        $slider = Slider::factory()->create(['name' => 'Current hero']);
        $video = Asset::factory()->create(['kind' => 'video', 'visibility' => 'public']);
        $slider->slides()->create(['title' => ['ar' => 'هيرو الرئيسية', 'en' => 'Home hero test'], 'description' => ['ar' => 'تجربة فيديو', 'en' => 'Video test'], 'desktop_media_id' => $video->id, 'desktop_media_type' => 'video', 'is_active' => true, 'sort_order' => 0]);

        $this->assertSame('home_hero', $oldSlider->refresh()->location);
        $this->assertFalse($oldSlider->is_active);
        $this->assertSame('home_hero', $slider->refresh()->location);
        $this->assertArrayNotHasKey('location', ModuleRegistry::get('sliders')['fields']);

        $response = $this->get('/en')->assertOk()->assertSee('Home hero test')->assertSee('id="home-hero"', false)->assertSee('hero-media', false);
        $this->assertSame(1, substr_count($response->getContent(), '<h3 class="slide-title"'));
        $response->assertSee('preload="none"', false)->assertSee('data-src=', false);

        $this->get('/en/'.$page->text('slug', 'en'))->assertOk()->assertDontSee('Home hero test')->assertDontSee('id="home-hero"', false);
    }

    public function test_home_slide_can_render_media_without_any_copy(): void
    {
        $this->assertFalse(ModuleRegistry::get('slides')['fields']['title']['required']);

        $slider = Slider::factory()->create(['name' => 'Media only hero']);
        $image = Asset::factory()->create(['kind' => 'image', 'visibility' => 'public']);
        Livewire::actingAs($this->owner())
            ->test(CreateSlideRecord::class)
            ->fillForm([
                'slider_id' => $slider->id,
                'title' => ['ar' => '', 'en' => ''],
                'description' => ['ar' => '', 'en' => ''],
                'button_text' => ['ar' => '', 'en' => ''],
                'button_url' => null,
                'desktop_media_id' => $image->id,
                'desktop_media_type' => 'image',
                'is_active' => true,
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('slides', ['slider_id' => $slider->id, 'desktop_media_id' => $image->id]);

        $this->get('/ar')
            ->assertOk()
            ->assertSee('class="hero-media"', false)
            ->assertDontSee('class="hero-content"', false)
            ->assertDontSee('class="slide-title"', false)
            ->assertDontSee('hero-kicker', false)
            ->assertDontSee('hero-signature', false)
            ->assertDontSee('Digital solutions · Cairo');
    }

    public function test_home_displays_at_most_nine_projects_with_the_original_card_layout(): void
    {
        Project::factory()->count(10)->create(['status' => 'published']);

        $response = $this->get('/ar')
            ->assertOk()
            ->assertSee('class="projects-grid"', false)
            ->assertDontSee('projects-grid--showcase');

        $this->assertSame(9, substr_count($response->getContent(), '<article class="project-card">'));
    }

    public function test_home_intro_section_content_and_image_are_managed_by_settings(): void
    {
        $image = Asset::factory()->create(['kind' => 'image', 'visibility' => 'public', 'is_active' => true]);
        $homeSettings = SettingsRegistry::groups()['home'];

        $this->assertSame('asset', $homeSettings['intro_media'][0]);
        $this->assertSame('media_fit', $homeSettings['intro_media_fit'][0]);
        Livewire::actingAs($this->owner())
            ->test(StudioSettings::class)
            ->assertFormFieldExists('home.intro_enabled')
            ->assertFormFieldExists('home.intro_media')
            ->assertFormFieldExists('home.intro_title.ar')
            ->assertFormFieldExists('home.intro_title.en')
            ->assertFormFieldExists('home.intro_link_url');

        foreach ([
            'intro_enabled' => true,
            'intro_media_enabled' => true,
            'intro_media' => $image->id,
            'intro_media_fit' => 'contain',
            'intro_frame_enabled' => false,
            'intro_media_background' => '#105666',
            'intro_media_caption_color' => '#F7F4D5',
            'intro_media_caption' => ['ar' => 'نص الصورة المُدار', 'en' => 'Managed image caption'],
            'intro_label' => ['ar' => 'عنوان تمهيدي مُدار', 'en' => 'Managed eyebrow'],
            'intro_title' => ['ar' => 'عنوان مُدار', 'en' => 'Managed introduction title'],
            'intro_text' => ['ar' => 'وصف مُدار', 'en' => 'Managed introduction copy'],
            'intro_link_enabled' => true,
            'intro_link_text' => ['ar' => 'اعرف أكثر', 'en' => 'Learn more'],
            'intro_link_url' => 'https://example.com/about',
            'intro_link_new_tab' => true,
            'intro_founder_label' => ['ar' => 'وصف المؤسس', 'en' => 'Managed founder label'],
            'intro_signature' => ['ar' => 'توقيع', 'en' => 'Managed signature'],
        ] as $key => $value) {
            Studio::put('home.'.$key, $value, 'home');
        }
        Studio::flush();

        $this->get('/en')
            ->assertOk()
            ->assertSee('id="home-intro"', false)
            ->assertSee('intro-mark--asset', false)
            ->assertSee('intro-mark--no-frame', false)
            ->assertSee('--intro-media-background:#105666', false)
            ->assertSee('--intro-media-fit:contain', false)
            ->assertSee('Managed image caption')
            ->assertSee('Managed introduction title')
            ->assertSee('Managed introduction copy')
            ->assertSee('Learn more')
            ->assertSee('target="_blank"', false)
            ->assertSee('Managed founder label')
            ->assertSee('Managed signature');

        Studio::put('home.intro_media_caption', ['ar' => null, 'en' => null], 'home');
        Studio::put('home.intro_founder_label', ['ar' => null, 'en' => null], 'home');
        Studio::flush();
        $this->get('/en')
            ->assertOk()
            ->assertDontSee('Managed image caption')
            ->assertDontSee('WE CONNECT')
            ->assertDontSee('Managed founder label')
            ->assertDontSee('Founder & technical lead');

        Studio::put('home.intro_enabled', false, 'home');
        Studio::flush();
        $this->get('/en')->assertOk()->assertDontSee('id="home-intro"', false);
    }

    public function test_public_cta_arrows_use_svg_instead_of_an_emoji_prone_glyph(): void
    {
        MethodologyStep::query()->create([
            'title' => ['ar' => 'نرسم الطريق', 'en' => 'Map the path'],
            'icon' => '↗',
            'number' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        foreach (['/ar', '/ar/contact'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('class="arrow-up-right-icon"', false)
                ->assertDontSee('↗', false);
        }
    }

    public function test_footer_has_four_dynamic_columns_and_uses_dashboard_contact_settings(): void
    {
        Studio::put('general.email', 'islamwebstudio@info.com');
        Studio::put('general.phone', '01114292011');
        Studio::put('general.socials', [
            ['label' => 'Facebook', 'url' => 'https://facebook.com/islam-web-studio'],
            ['label' => 'Instagram', 'url' => 'https://instagram.com/islam_web_studio'],
            ['label' => 'TikTok', 'url' => 'https://tiktok.com/@islam_webstudio'],
        ]);
        Service::factory()->create([
            'name' => ['ar' => 'خدمة تظهر في الفوتر', 'en' => 'Footer service'],
            'slug' => ['ar' => 'خدمة-الفوتر', 'en' => 'footer-service'],
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->get('/ar')
            ->assertOk()
            ->assertSee('islamwebstudio@info.com')
            ->assertSee('01114292011')
            ->assertSee('خدمة تظهر في الفوتر')
            ->assertSee('aria-label="Facebook"', false)
            ->assertSee('aria-label="Instagram"', false)
            ->assertSee('aria-label="TikTok"', false)
            ->assertSee('class="whatsapp-float"', false)
            ->assertSee('href="https://wa.me/201114292011"', false)
            ->assertDontSee('demo-note', false)
            ->assertDontSee(Studio::text('demo_notice'));

        $this->assertSame(4, substr_count($response->getContent(), 'data-footer-column='));
        $this->assertSame('islamwebstudio@info.com', SettingsRegistry::defaults()['general.email']);
        $this->assertCount(3, SettingsRegistry::defaults()['general.socials']);
        $this->assertSame('201114292011', Studio::whatsappNumber());
    }

    public function test_inactive_user_cannot_access_dashboard_and_expired_export_is_unavailable(): void
    {
        $owner = $this->owner();
        $owner->update(['is_active' => false]);
        $this->actingAs($owner)->get('/admin')->assertForbidden();
        $owner->update(['is_active' => true]);
        Storage::fake('local');
        Storage::disk('local')->put('exports/expired.xlsx', 'test');
        $run = ExportRun::create(['user_id' => $owner->id, 'module' => 'leads', 'status' => 'completed', 'file_path' => 'exports/expired.xlsx', 'created_at' => now()->subDays(8)]);
        $this->actingAs($owner)->get('/exports/'.$run->id.'/download')->assertNotFound();
    }

    public function test_deleted_roles_can_be_restored_without_losing_permissions(): void
    {
        $owner = $this->owner();
        $permission = Permission::findOrCreate('pages.view', 'web');
        $role = Role::findOrCreate('Temporary reviewer', 'web');
        $role->givePermissionTo($permission);
        $this->assertTrue($owner->can('delete', $role));
        $role->delete();
        $this->assertSoftDeleted('roles', ['id' => $role->id]);
        $role->restore();
        $this->assertTrue($role->hasPermissionTo('pages.view'));
        $this->assertFalse($owner->can('delete', Role::findByName('Owner', 'web')));
    }
}
