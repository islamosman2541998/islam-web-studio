<?php

namespace Database\Seeders;

use App\Jobs\BuildExport;
use App\Models\Asset;
use App\Models\ExportRun;
use App\Models\Lead;
use App\Models\MenuItem;
use App\Models\MenuLocation;
use App\Models\MethodologyStep;
use App\Models\Page;
use App\Models\PageMedia;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectMedia;
use App\Models\ProjectMetric;
use App\Models\Redirect;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceFeature;
use App\Models\ServicePackage;
use App\Models\Setting;
use App\Models\Slide;
use App\Models\Slider;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\Translation;
use App\Models\User;
use App\Support\MediaPipeline;
use App\Support\ModuleRegistry;
use App\Support\SettingsRegistry;
use App\Support\Studio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class StudioDemoSeeder extends Seeder
{
    private function bi(string $ar, string $en): array
    {
        return ['ar' => $ar, 'en' => $en];
    }

    private function content(string $class, string $slug, array $data): mixed
    {
        return $class::firstOrCreate(['slug->en' => $slug], ['slug' => $this->bi($slug, $slug), 'status' => 'published', 'is_active' => true, 'sort_order' => 0, ...$data]);
    }

    private function asset(string $filename, string $ar, string $en, string $visibility = 'public'): Asset
    {
        if ($asset = Asset::where('name->en', $en)->first()) {
            if ($asset->upload_path) {
                app(MediaPipeline::class)->process($asset);
            }

            return $asset;
        }
        $path = 'incoming/demo-'.basename($filename);
        Storage::disk('local')->put($path, file_get_contents(__DIR__.'/assets/'.$filename));
        $asset = Asset::createQuietly(['name' => $this->bi($ar, $en), 'alt' => $this->bi($ar, $en), 'caption' => $this->bi('تصميم من إسلام ويب ستوديو', 'Design by Islam Web Studio'), 'upload_path' => $path, 'visibility' => $visibility, 'is_active' => true]);
        app(MediaPipeline::class)->process($asset);

        return $asset->refresh();
    }

    public function run(): void
    {
        if (! config('studio.demo')) {
            throw new \RuntimeException('Demo seeding requires DEMO_MODE=true. Production data will not be seeded.');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissionNames = ['dashboard.view', 'settings.update'];
        foreach (ModuleRegistry::all() as $module => $definition) {
            foreach (['view', 'create', 'update', 'delete', 'export'] as $action) {
                $permissionNames[] = $module.'.'.$action;
            }
        }
        foreach ($permissionNames as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        $owner = Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        $owner->syncPermissions($permissionNames);
        $editor = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $editor->syncPermissions(array_values(array_filter($permissionNames, fn ($p) => $p === 'dashboard.view' || (! str_starts_with($p, 'leads.') && ! str_starts_with($p, 'export_runs.') && $p !== 'settings.update' && ! str_ends_with($p, '.delete')))));
        $sales = Role::firstOrCreate(['name' => 'Sales', 'guard_name' => 'web']);
        $sales->syncPermissions(['dashboard.view', 'leads.view', 'leads.create', 'leads.update', 'leads.export', 'export_runs.view']);
        $viewer = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions(['dashboard.view', 'services.view', 'projects.view', 'posts.view', 'pages.view']);
        $email = env('DEMO_ADMIN_EMAIL');
        $password = env('DEMO_ADMIN_PASSWORD');
        if (! $email || strlen((string) $password) < 12) {
            throw new \RuntimeException('Set local demo admin credentials before seeding.');
        }
        $admin = User::firstOrCreate(['email' => $email], ['name' => 'Islam — Studio owner', 'password' => $password, 'is_active' => true, 'locale' => 'ar']);
        $admin->assignRole($owner);
        foreach (['Editor', 'Sales', 'Viewer'] as $role) {
            $user = User::firstOrCreate(['email' => strtolower($role).'@islamwebstudio.test'], ['name' => 'Demo '.$role, 'password' => Str::random(40), 'is_active' => true, 'locale' => 'ar']);
            $user->assignRole($role);
        }
        foreach (SettingsRegistry::defaults() as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['group' => explode('.', $key)[0], 'value' => $value]);
        }
        $olive = $this->asset('olive.png', 'أوليف — متجر أثاث', 'Olive — commerce');
        $namaa = $this->asset('namaa.png', 'نماء — لوحة ERP', 'Namaa — ERP');
        $mizan = $this->asset('mizan.png', 'ميزان — أتمتة', 'Mizan — automation');
        $forma = $this->asset('forma.png', 'فورما — هوية', 'Forma — identity');
        $oliveMobile = $this->asset('olive-mobile.png', 'أوليف — موبايل', 'Olive — mobile');
        $namaaMobile = $this->asset('namaa-mobile.png', 'نماء — موبايل', 'Namaa — mobile');
        $logo = $this->asset('logo-reference.jpeg', 'لوجو إسلام ويب ستوديو الأصلي', 'Original Islam Web Studio logo');
        $video = $this->asset('flower-demo.mp4', 'فيديو زهور — MDN', 'Flower video — MDN');
        $video->update(['caption' => $this->bi('فيديو تعليمي عام من MDN.', 'Public educational video from MDN.')]);
        $brief = __DIR__.'/assets/project-brief-demo.zip';
        if (! is_file($brief)) {
            $zip = new \ZipArchive;
            $zip->open($brief, \ZipArchive::CREATE);
            $zip->addFromString('project-brief.txt', "Islam Web Studio — Project brief\n\nProject name:\nBusiness goal:\nAudience:\nRequired pages:\nUseful references:\nLaunch window:\n");
            $zip->close();
        }
        $publicFile = $this->asset('project-brief-demo.zip', 'نموذج وصف مشروع', 'Project brief — download');
        $privateFile = $this->asset('project-brief-demo.zip', 'ملف داخلي خاص', 'Private internal file', 'private');
        Studio::put('seo.og_image', $olive->id, 'seo');
        $dev = $this->content(ServiceCategory::class, 'development', ['name' => $this->bi('التطوير والأنظمة', 'Development & systems'), 'description' => $this->bi('حلول رقمية مبنية على احتياجك.', 'Digital solutions built around your needs.')]);
        $creative = $this->content(ServiceCategory::class, 'creative', ['name' => $this->bi('التصميم والمحتوى', 'Design & content'), 'description' => $this->bi('هوية واضحة ومحتوى يوصل فكرتك.', 'A clear identity and content that gets your idea across.')]);
        $growth = $this->content(ServiceCategory::class, 'growth', ['name' => $this->bi('التسويق والنمو', 'Marketing & growth'), 'description' => $this->bi('خطوات مدروسة للوصول لجمهورك.', 'Thoughtful steps to reach your audience.')]);
        $services = [];
        $serviceData = [
            ['web-development', 'مواقع تعريفية ولاندينج بيدج', 'Websites & landing pages', 'موقع يعبر عن شغلك، وتجربة تسهّل على عميلك ياخد الخطوة الجاية.', 'A website that reflects your work and makes the next step clear.', $dev, $olive],
            ['ecommerce', 'متاجر إلكترونية', 'E-commerce', 'متجر متكامل لإدارة منتجاتك وطلباتك، بتجربة شراء بسيطة من الموبايل.', 'A complete store for your products and orders, with an effortless mobile experience.', $dev, $olive],
            ['laravel-systems', 'أنظمة Laravel مخصصة', 'Custom Laravel systems', 'نحول خطوات شغلك لنظام مرن، بصلاحيات واضحة وتقارير مفيدة.', 'Turn your day-to-day workflow into a flexible system with clear access and useful reports.', $dev, $namaa],
            ['erp', 'أنظمة ERP', 'ERP systems', 'اربط المبيعات والمخزون والعملاء والتقارير في مكان واحد.', 'Connect sales, inventory, customers and reporting in one place.', $dev, $namaa],
            ['ai-automation', 'أتمتة وذكاء اصطناعي', 'AI & automation', 'قلل التكرار واربط الأدوات اللي بتستخدمها، مع مراجعة بشرية في الخطوات المهمة.', 'Reduce repetition and connect your tools, with human review where it matters.', $dev, $mizan],
            ['design-branding', 'تصميم وهوية بصرية', 'Design & visual identity', 'لغة بصرية متناسقة تخلي البراند واضح ومميز في كل نقطة تواصل.', 'A consistent visual language that makes your brand clear at every touchpoint.', $creative, $forma],
            ['video-content', 'فيديو ومحتوى', 'Video & content', 'فيديوهات وتصاميم توصل الرسالة بسرعة وتناسب كل منصة.', 'Videos and visuals that get the message across and fit each platform.', $creative, $forma],
            ['social-media', 'إدارة صفحات التواصل', 'Social media management', 'خطة محتوى، نشر ومتابعة، وتقرير يوضح إيه اللي اتعمل وإيه اللي جاي.', 'Content planning, publishing, community follow-up and clear reporting.', $growth, $forma],
            ['paid-ads', 'إعلانات ممولة', 'Paid advertising', 'نحدد الهدف والجمهور ونختبر الرسائل، ونقيس النتيجة بوضوح.', 'Define goals and audiences, test the message, and measure results clearly.', $growth, $mizan],
        ];
        foreach ($serviceData as $i => [$slug,$ar,$en,$shortAr,$shortEn,$category,$image]) {
            $service = $this->content(Service::class, $slug, ['name' => $this->bi($ar, $en), 'short_description' => $this->bi($shortAr, $shortEn), 'long_description' => $this->bi('<h2>نبدأ بفهم مشروعك</h2><p>'.$shortAr.'</p><p>بنحدد نطاق العمل والتسليمات معاك، ونبني تجربة مناسبة لجمهورك. كل مرحلة لها مراجعة واضحة قبل ما ننتقل للي بعدها.</p><h2>حل تقدر تديره وتطوره</h2><p>نهتم بسرعة الاستخدام، سهولة الإدارة، وتجربة الموبايل. ونسلمك توثيق يساعدك تتعامل مع الحل بثقة.</p>', '<h2>We start with your business</h2><p>'.$shortEn.'</p><p>We agree the scope and deliverables, then build around your audience. Each stage has a clear review before the next one begins.</p><h2>Built to manage and grow</h2><p>We focus on usability, mobile experience, and practical administration. Handover includes documentation so you can use the result with confidence.</p>'), 'service_category_id' => $category->id, 'image_media_id' => $image->id, 'pricing_type' => $i === 0 ? 'packages' : 'on_request', 'is_featured' => $i < 6, 'sort_order' => $i, 'meta_title' => $this->bi($ar.' | إسلام ويب ستوديو', $en.' | Islam Web Studio'), 'meta_description' => $this->bi($shortAr, $shortEn), 'og_image_id' => $image->id]);
            $services[$slug] = $service;
            foreach ([['جلسة لفهم المتطلبات', 'Discovery & requirements'], ['تصميم وتجربة مناسبة للموبايل', 'Mobile-first design & experience'], ['مراجعة واختبار قبل التسليم', 'Review & testing before handover'], ['توثيق ودعم بعد الإطلاق', 'Documentation & launch support']] as $j => [$fa,$fe]) {
                ServiceFeature::firstOrCreate(['service_id' => $service->id, 'sort_order' => $j], ['text' => $this->bi($fa, $fe)]);
            }
        }
        foreach ([['انطلاقة', 'Launch', 12000], ['نمو', 'Grow', 22000], ['توسع', 'Scale', 35000]] as $i => [$ar,$en,$price]) {
            ServicePackage::firstOrCreate(['service_id' => $services['web-development']->id, 'sort_order' => $i], ['name' => $this->bi($ar, $en), 'price' => $price, 'currency' => 'EGP', 'features' => $this->bi("تصميم مخصص\nعربي وإنجليزي\nلوحة تحكم\nإعداد SEO أساسي", "Custom design\nArabic & English\nAdmin dashboard\nSEO foundations")]);
        }
        $cats = [];
        foreach ([['websites', 'مواقع ومتاجر', 'Websites & commerce'], ['systems', 'أنظمة وأتمتة', 'Systems & automation'], ['branding', 'تصميم ومحتوى', 'Design & content']] as [$slug,$ar,$en]) {
            $cats[$slug] = $this->content(ProjectCategory::class, $slug, ['name' => $this->bi($ar, $en), 'description' => $this->bi('أعمال مختارة تعرض طريقة التفكير والتنفيذ.', 'Selected work that reflects our thinking and execution.')]);
        }
        $projects = [];
        $projectData = [
            ['olive-living', 'أوليف — تجربة تسوق أقرب للطبيعة', 'Olive — a considered shopping experience', 'websites', $olive, $oliveMobile, 'ecommerce'],
            ['namaa-erp', 'نماء — كل تفاصيل الشغل، متصلة', 'Namaa — a connected business', 'systems', $namaa, $namaaMobile, 'erp'],
            ['mizan-automation', 'ميزان — وقت أكتر للشغل المهم', 'Mizan — time for meaningful work', 'systems', $mizan, null, 'ai-automation'],
            ['forma-identity', 'فورما — هوية لها حضور', 'Forma — an identity with presence', 'branding', $forma, null, 'design-branding'],
            ['bayt-platform', 'بيت — منصة عقارية أوضح', 'Bayt — a clearer property platform', 'websites', $olive, $oliveMobile, 'web-development'],
            ['sahm-dashboard', 'سهم — قرارات مبنية على بيانات', 'Sahm — decisions shaped by data', 'systems', $namaa, $namaaMobile, 'laravel-systems'],
            ['wasl-campaign', 'وصل — حملة تربط المحتوى بالنتيجة', 'Wasl — content connected to results', 'branding', $forma, null, 'social-media'],
            ['mowj-store', 'موج — متجر بتجربة شراء أسرع', 'Mowj — a faster commerce journey', 'websites', $olive, $oliveMobile, 'ecommerce'],
            ['flow-automation', 'فلو — عمليات أقل تكرارًا', 'Flow — less repetitive operations', 'systems', $mizan, null, 'ai-automation'],
        ];
        foreach ($projectData as $i => [$slug,$ar,$en,$category,$image,$mobile,$service]) {
            $project = $this->content(Project::class, $slug, ['title' => $this->bi($ar, $en), 'project_category_id' => $cats[$category]->id, 'client' => 'Islam Web Studio', 'duration' => $this->bi('٦ أسابيع', '6 weeks'), 'overview' => $this->bi('تصميم رقمي يربط هدف المشروع بتجربة استخدام واضحة ومتناسقة على الكمبيوتر والموبايل.', 'A digital experience that connects the project goal to a clear, consistent journey across desktop and mobile.'), 'challenge' => $this->bi('<p>تبسيط رحلة المستخدم مع الحفاظ على شخصية البراند، وتجميع التفاصيل في تجربة واحدة متناسقة.</p>', '<p>Simplify the customer journey while preserving the brand’s character and bringing the details together.</p>'), 'solution' => $this->bi('<p>بدأنا بخريطة للمحتوى والرحلة، وصممنا واجهة واضحة، ثم ربطناها بإدارة مرنة ومكونات قابلة للتطوير.</p>', '<p>We mapped content and journeys, designed a clear interface, then connected it to flexible administration and reusable components.</p>'), 'result' => $this->bi('<p>تجربة رقمية متكاملة بواجهة واضحة ومكونات مرنة قابلة للتطوير.</p>', '<p>A complete digital experience with a clear interface and flexible components built to evolve.</p>'), 'main_media_type' => 'image', 'main_media_id' => $image->id, 'main_media_mobile_id' => $mobile?->id, 'tech_stack' => ['Laravel', 'Livewire', 'Tailwind'], 'is_featured' => true, 'sort_order' => $i, 'og_image_id' => $image->id]);
            $project->services()->syncWithoutDetaching([$services[$service]->id]);
            $projects[] = $project;
            ProjectMedia::firstOrCreate(['project_id' => $project->id, 'sort_order' => 0], ['media_id' => $image->id, 'type' => 'image', 'caption' => $this->bi('تصميم سطح المكتب', 'Desktop experience')]);
            ProjectMedia::firstOrCreate(['project_id' => $project->id, 'sort_order' => 1], ['media_id' => ($mobile ?? $image)->id, 'type' => 'image', 'caption' => $this->bi('تفاصيل تجربة الاستخدام', 'Experience details')]);
            ProjectMetric::firstOrCreate(['project_id' => $project->id, 'sort_order' => 0], ['label' => $this->bi('تقليل خطوات الاستخدام', 'Fewer user steps'), 'value' => '−30%']);
            ProjectMetric::firstOrCreate(['project_id' => $project->id, 'sort_order' => 1], ['label' => $this->bi('لغات التجربة', 'Experience languages'), 'value' => 'AR / EN']);
        }
        ProjectMedia::firstOrCreate(['project_id' => $projects[3]->id, 'sort_order' => 2], ['media_id' => $video->id, 'type' => 'video', 'caption' => $this->bi('فيديو تعليمي من MDN', 'Educational video from MDN')]);
        $home = $this->content(Page::class, 'home', ['title' => $this->bi('شريكك من الفكرة إلى التأثير', 'Your partner, from idea to impact'), 'template' => 'home', 'content' => $this->bi('', '')]);
        $about = $this->content(Page::class, 'studio', ['title' => $this->bi('عقول فضولية. شغل له معنى.', 'Curious minds. Meaningful work.'), 'template' => 'about', 'hero_media_id' => $forma->id, 'excerpt' => $this->bi('إسلام ويب ستوديو: تطوير وتصميم وتسويق في رحلة واحدة واضحة.', 'Islam Web Studio: development, design and marketing in one clear journey.'), 'content' => $this->bi('<h2>أهلًا، أنا إسلام</h2><p>مطور Full Stack متخصص في PHP وLaravel، ومؤسس Islam Web Studio. بنبني مواقع ومتاجر وأنظمة مخصصة، ونكمل التجربة بالتصميم والمحتوى والتسويق.</p><h2>فكرة واحدة، تخصصات متصلة</h2><p>بنبدأ بفهم اللي مشروعك محتاجه فعلًا، ونوضح نطاق العمل والتكلفة والتسليمات قبل التنفيذ. نهتم بالتفاصيل الصغيرة اللي بتخلي الحل أسهل وأوضح لعميلك وفريقك.</p><h2>إزاي نقدر نفيدك؟</h2><p>سواء بتبدأ من الصفر أو بتطور تجربة موجودة، بنرتب الأولويات ونبني الحل خطوة خطوة. هدفنا تسليم شغل تقدر تستخدمه وتديره بثقة.</p>', '<h2>Hello, I’m Islam</h2><p>A full stack developer specializing in PHP and Laravel, and founder of Islam Web Studio. We build websites, commerce and custom systems, then connect them with design, content and marketing.</p><h2>One idea. Connected disciplines.</h2><p>We start by understanding what your business actually needs. Scope, cost and deliverables are clear before implementation. We care about the details that make the result easier for your customers and your team.</p><h2>How can we help?</h2><p>Whether you are starting from scratch or improving an existing experience, we organize priorities and build step by step. Our aim is work you can use and manage with confidence.</p>')]);
        $briefPage = $this->content(Page::class, 'start-here', ['title' => $this->bi('قبل ما نبدأ مشروعك', 'Before we start'), 'excerpt' => $this->bi('أسئلة بسيطة تساعدنا نفهم فكرتك.', 'A few questions to help us understand your idea.'), 'hero_media_id' => $mizan->id, 'content' => $this->bi('<h2>احكيلنا عن الهدف</h2><p>مين جمهورك؟ إيه الخطوة اللي عايز الزائر يعملها؟ وإيه أهم مشكلة بتحاول تحلها؟</p><h2>مراجع وتفاصيل</h2><p>جمع أي مراجع بصرية، قائمة بالصفحات أو الوظائف، وحدد موعدًا مناسبًا للإطلاق.</p>', '<h2>Tell us about the goal</h2><p>Who is your audience? What should visitors do? What is the main problem you need to solve?</p><h2>References and details</h2><p>Gather visual references, a list of pages or features, and a realistic launch window.</p>')]);
        PageMedia::firstOrCreate(['page_id' => $briefPage->id, 'sort_order' => 0], ['media_id' => $publicFile->id, 'kind' => 'file', 'caption' => $this->bi('تحميل نموذج وصف المشروع', 'Download the project brief')]);
        PageMedia::firstOrCreate(['page_id' => $briefPage->id, 'sort_order' => 1], ['media_id' => $olive->id, 'kind' => 'gallery_image', 'caption' => $this->bi('مثال لتجربة متجر', 'A commerce experience example')]);
        PageMedia::firstOrCreate(['page_id' => $briefPage->id, 'sort_order' => 2], ['media_id' => $video->id, 'kind' => 'video', 'caption' => $this->bi('فيديو داخل المعرض', 'Gallery video')]);
        $privacy = $this->content(Page::class, 'privacy', ['title' => $this->bi('سياسة الخصوصية', 'Privacy policy'), 'content' => $this->bi('<p>يحفظ نموذج التواصل الاسم والبريد الإلكتروني ورقم الهاتف والرسالة داخل قاعدة بيانات الموقع، ولا يطّلع عليها إلا المستخدمون المصرح لهم من لوحة التحكم.</p><p>نستخدم البيانات للرد على طلبك ومتابعة المشروع فقط، ويمكنك طلب تصحيحها أو حذفها عبر بيانات التواصل المنشورة في الموقع.</p>', '<p>The contact form stores your name, email address, phone number and message in the website database. Only authorized dashboard users can access it.</p><p>We use this information only to respond to your enquiry and follow up on the project. You may request correction or deletion through the contact details published on the website.</p>')]);
        $this->content(Page::class, 'draft-example', ['title' => $this->bi('صفحة مسودة للتجربة', 'Draft page example'), 'status' => 'draft', 'excerpt' => $this->bi('هذه الصفحة غير منشورة.', 'This page is not published.')]);
        $slider = Slider::firstOrCreate(['location' => 'home_hero'], ['name' => 'Home hero', 'autoplay' => true, 'autoplay_speed' => 6500, 'draggable' => true, 'arrows' => true, 'dots' => true, 'loop' => true, 'is_active' => true]);
        $slides = [[$this->bi("فكرتك تستاهل\nشغل يليق بيها.", "Your idea.\nThoughtfully built."), $this->bi('من أول سطر كود، لآخر تفصيلة في البراند. بنربط التطوير والتصميم والتسويق عشان مشروعك ياخد خطوته الجاية.', 'From the first line of code to the final brand detail. We connect development, design and marketing to move your business forward.'), $olive, $oliveMobile, 'image'], [$this->bi("شغل أقل تكرارًا.\nفرص أكتر للنمو.", "Less repetition.\nMore possibility."), $this->bi('أنظمة مخصصة وأتمتة مدروسة تخلي وقتك للقرارات المهمة.', 'Custom systems and considered automation give you more time for decisions that matter.'), $namaa, $namaaMobile, 'image'], [$this->bi("صورة واضحة.\nحكاية توصل.", "A clear identity.\nA story that connects."), $this->bi('تصميم ومحتوى وفيديو يناسبوا شخصيتك.', 'Design, content and motion that fit your character.'), $video, $video, 'video']];
        foreach ($slides as $i => [$title,$desc,$desktop,$mobile,$type]) {
            Slide::firstOrCreate(['slider_id' => $slider->id, 'sort_order' => $i], ['title' => $title, 'description' => $desc, 'desktop_media_type' => $type, 'desktop_media_id' => $desktop->id, 'desktop_poster_id' => $type === 'video' ? $forma->id : null, 'mobile_media_type' => $type, 'mobile_media_id' => $mobile->id, 'mobile_poster_id' => $type === 'video' ? $forma->id : null, 'button_text' => $this->bi('خلينا نبدأ', 'Let’s get started'), 'button_url' => '/{locale}/contact', 'button_color' => '#D3968C', 'button_text_color' => '#0A3323', 'button_hover_color' => '#F7F4D5', 'text_color' => '#F7F4D5', 'overlay_opacity' => 0, 'video_advance' => 'ended', 'is_active' => true]);
        }
        $postCats = [];
        foreach ([['development', 'تطوير المواقع', 'Development'], ['design', 'تصميم وتجربة', 'Design & experience'], ['marketing', 'تسويق ومحتوى', 'Marketing & content']] as [$slug,$ar,$en]) {
            $postCats[$slug] = $this->content(PostCategory::class, $slug, ['name' => $this->bi($ar, $en)]);
        }
        $tags = [];
        foreach (['Laravel', 'UI-UX', 'SEO', 'Automation'] as $tag) {
            $tags[] = Tag::firstOrCreate(['slug->en' => strtolower($tag)], ['name' => $this->bi($tag, $tag), 'slug' => $this->bi(strtolower($tag), strtolower($tag)), 'is_active' => true]);
        }
        $posts = [['before-your-website', '٥ أسئلة قبل ما تبدأ موقعك', '5 questions before you build your website', 'development', $olive], ['clear-design', 'التصميم الواضح بيبدأ من المحتوى', 'Clear design starts with content', 'design', $forma], ['automation-first-step', 'إزاي تختار أول مهمة تعملها أتمتة؟', 'Choosing your first automation', 'development', $mizan], ['measure-what-matters', 'قيس اللي يفيد مشروعك فعلًا', 'Measure what matters to your business', 'marketing', $namaa], ['mobile-experience', 'الموبايل مش نسخة أصغر من الموقع', 'Mobile is not just a smaller website', 'design', $oliveMobile], ['scheduled-example', 'مقال مجدول للتجربة', 'Scheduled article example', 'development', $namaa]];
        foreach ($posts as $i => [$slug,$ar,$en,$category,$image]) {
            $post = $this->content(Post::class, $slug, ['title' => $this->bi($ar, $en), 'excerpt' => $this->bi('خطوات عملية تساعدك ترتب الأولويات وتبني تجربة أوضح لجمهورك.', 'Practical steps to organize priorities and build a clearer experience for your audience.'), 'content' => $this->bi('<h2>ابدأ بالسؤال الصح</h2><p>قبل اختيار الأدوات أو شكل التصميم، اسأل: إيه الهدف؟ مين المستخدم؟ وإيه الخطوة اللي محتاج يسهل عليه يعملها؟ الإجابات دي بتقلل التشتت وبتخلي القرارات أوضح.</p><h2>رتب الأولويات</h2><p>اكتب الاحتياجات الأساسية، وافصل بينها وبين الإضافات اللي ممكن تيجي بعدين. نسخة أولى مركزة أسهل في الاختبار والتطوير.</p><ul><li>حدد هدفًا واضحًا يمكن مراجعته.</li><li>اختبر الرحلة على الموبايل من البداية.</li><li>اجمع ملاحظات حقيقية قبل التوسع.</li></ul><h2>راجع وتعلم</h2><p>بعد الإطلاق، راقب الاستخدام والأسئلة المتكررة. التحسينات الصغيرة المبنية على ملاحظات واضحة ممكن تصنع فرقًا كبيرًا.</p><blockquote>الأداة المناسبة بتخدم الفكرة، والفكرة الواضحة بتسهل اختيار الأداة.</blockquote>', '<h2>Start with the right question</h2><p>Before choosing tools or visuals, ask: what is the goal, who is the user, and what action should be easier? Those answers reduce noise and make decisions clearer.</p><h2>Organize priorities</h2><p>Write down the essentials and separate them from later additions. A focused first release is easier to test and improve.</p><ul><li>Define a clear goal you can review.</li><li>Test the mobile journey from the start.</li><li>Gather real feedback before expanding.</li></ul><h2>Review and learn</h2><p>After launch, observe usage and recurring questions. Small improvements based on clear feedback can make a meaningful difference.</p><blockquote>The right tool serves the idea. A clear idea makes the tool easier to choose.</blockquote>'), 'featured_media_id' => $image->id, 'post_category_id' => $postCats[$category]->id, 'author_id' => $admin->id, 'published_at' => $i === 5 ? now()->addDays(10) : now()->subDays($i * 6 + 1), 'is_featured' => $i < 3, 'sort_order' => $i, 'og_image_id' => $image->id]);
            $post->tags()->syncWithoutDetaching([$tags[$i % count($tags)]->id]);
        }
        foreach ([['عميل تجريبي — أوليف', 'Demo client — Olive', 0], ['عميل تجريبي — نماء', 'Demo client — Namaa', 1], ['عميل تجريبي — فورما', 'Demo client — Forma', 3], ['تقييم بانتظار المراجعة', 'Review awaiting approval', 2]] as $i => [$ar,$en,$projectIndex]) {
            $review = Testimonial::firstOrCreate(['client_name' => $en], ['client_company' => $this->bi($ar, $en), 'quote' => $this->bi('هذا رأي تجريبي لعرض شكل التقييمات. استبدله برأي موثق من عميلك قبل نشر الموقع الحقيقي.', 'This is a sample review showing the testimonial layout. Replace it with verified client feedback before public launch.'), 'rating' => $i === 3 ? 4 : 5, 'is_approved' => $i < 3, 'is_active' => true, 'is_featured' => $i < 3, 'is_demo' => true, 'project_id' => $projects[$projectIndex]->id, 'sort_order' => $i]);
            if ($i < 3) {
                $projects[$projectIndex]->update(['testimonial_id' => $review->id]);
            }
        }
        $steps = [['نفهم الأول', 'Discover', 'نسمع فكرتك، نفهم جمهورك، ونحدد المشكلة اللي محتاجين نحلها.', 'We listen, understand your audience, and identify the problem to solve.', 'ملخص متطلبات وأهداف', 'Requirements & goals'], ['نرسم الطريق', 'Plan', 'نرتب الأولويات ونوضح نطاق العمل والمراحل والتسليمات.', 'We prioritize and agree the scope, milestones and deliverables.', 'خطة عمل وجدول واضح', 'A clear plan & timeline'], ['نصمم ونبني', 'Design & build', 'نحول الفكرة لتجربة حقيقية، بمراجعات منتظمة معاك.', 'We turn the idea into a working experience, with regular reviews.', 'تصميم وتطوير قابل للمراجعة', 'Reviewable design & development'], ['نختبر ونطلق', 'Test & launch', 'نراجع الوظائف والموبايل والأداء قبل التسليم والإطلاق.', 'We review functionality, mobile usability and performance before launch.', 'اختبار وتسليم وتدريب', 'Testing, handover & guidance'], ['نتابع ونطور', 'Support & improve', 'نتابع الاستخدام ونحدد تحسينات مبنية على ملاحظات واضحة.', 'We follow usage and identify improvements based on clear feedback.', 'متابعة وخطة تحسين', 'Follow-up & improvement plan']];
        foreach ($steps as $i => [$ar,$en,$da,$de,$oa,$oe]) {
            MethodologyStep::firstOrCreate(['number' => $i + 1], ['title' => $this->bi($ar, $en), 'description' => $this->bi($da, $de), 'deliverable' => $this->bi($oa, $oe), 'icon' => ['◉', '↗', '✳', '✓', '+'][$i], 'sort_order' => $i, 'is_active' => true]);
        }
        foreach (['navbar' => $this->bi('القائمة الرئيسية', 'Main navigation'), 'mobile' => $this->bi('قائمة الموبايل', 'Mobile menu'), 'footer' => $this->bi('روابط الفوتر', 'Footer links')] as $key => $name) {
            $place = MenuLocation::firstOrCreate(['key' => $key], ['name' => $name, 'is_active' => true]);
            $menu = [['home', 'الرئيسية', 'Home'], ['services', 'الخدمات', 'Services'], ['projects', 'الأعمال', 'Work'], ['about', 'من نحن', 'Studio'], ['posts', 'المدونة', 'Journal']];
            if ($key !== 'navbar') {
                $menu[] = ['contact', 'تواصل معنا', 'Contact'];
                $menu[] = ['methodology', 'منهجيتنا', 'Our process'];
                $menu[] = ['testimonials', 'آراء العملاء', 'Testimonials'];
            }
            foreach ($menu as $i => [$route,$ar,$en]) {
                $isDynamic = in_array($route, ['services', 'projects']);
                MenuItem::firstOrCreate(['menu_location_id' => $place->id, 'sort_order' => $i, 'parent_id' => null], ['title' => $this->bi($ar, $en), 'type' => $isDynamic ? 'dynamic_group' : 'route', 'route_name' => $isDynamic ? null : ($route === 'posts' ? 'posts.index' : $route), 'dynamic_source' => $isDynamic ? $route : null, 'dynamic_mode' => 'rules', 'dynamic_featured' => true, 'dynamic_limit' => $route === 'services' ? 6 : 4, 'visibility' => 'all', 'is_active' => true]);
            }
            if ($key === 'footer') {
                MenuItem::firstOrCreate(['menu_location_id' => $place->id, 'sort_order' => 20], ['title' => $this->bi('ابدأ من هنا', 'Start here'), 'type' => 'page', 'page_id' => $briefPage->id, 'is_active' => true]);
                MenuItem::firstOrCreate(['menu_location_id' => $place->id, 'sort_order' => 21], ['title' => $this->bi('الخصوصية', 'Privacy'), 'type' => 'page', 'page_id' => $privacy->id, 'is_active' => true]);
            }
        }
        for ($i = 1; $i <= 25; $i++) {
            Lead::firstOrCreate(['email' => 'demo-lead-'.$i.'@example.test'], ['name' => 'عميل تجريبي '.$i, 'phone' => '+20 000 000 0000', 'service_id' => array_values($services)[($i - 1) % count($services)]->id, 'message' => 'طلب تجريبي: نرغب في تطوير تجربة رقمية للمشروع، ونحتاج معرفة الخطوات والتسليمات المقترحة.', 'source' => ['form', 'manual', 'whatsapp'][$i % 3], 'status' => ['new', 'contacted', 'quoted', 'won', 'lost'][($i - 1) % 5], 'internal_notes' => 'بيانات للعرض والتجربة فقط. لا تتواصل مع هذا العنوان.', 'follow_up_at' => now()->addDays($i % 7), 'locale' => $i % 2 ? 'ar' : 'en', 'is_demo' => true, 'created_at' => now()->subDays($i), 'updated_at' => now()->subDays($i)]);
        }
        foreach (['request_quote', 'contact_title', 'services_title', 'projects_title', 'posts_title', 'view_all', 'send', 'home', 'preview', 'export_excel'] as $key) {
            Translation::firstOrCreate(['key' => $key], ['value' => $this->bi(trans('studio.'.$key, [], 'ar'), trans('studio.'.$key, [], 'en')), 'is_active' => true]);
        }
        Redirect::firstOrCreate(['from_path' => '/en/old-studio'], ['to_path' => '/en/about', 'is_active' => true]);
        $run = ExportRun::firstOrCreate(['user_id' => $admin->id, 'module' => 'leads'], ['ids' => Lead::where('is_demo', true)->limit(5)->pluck('id')->all(), 'columns' => array_keys(ModuleRegistry::get('leads')['fields']), 'locale' => 'both', 'status' => 'queued', 'row_count' => 5]);
        if ($run->status !== 'completed') {
            BuildExport::dispatchSync($run->id);
        }
        Studio::flush();
        $this->command?->info('Demo content ready: 9 services, 9 projects, 6 posts (1 scheduled), 5 pages, 4 reviews, 5 steps, 25 enquiries, menus, media, roles and a private Excel export.');
    }
}
