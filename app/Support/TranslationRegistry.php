<?php

namespace App\Support;

class TranslationRegistry
{
    /**
     * @return array<string, array{label: array{ar: string, en: string}, tabs: array<string, array{label: array{ar: string, en: string}, fields: array<string, array{ar: string, en: string, type?: string}>}>}>
     */
    public static function groups(): array
    {
        return [
            'home' => [
                'label' => ['ar' => 'الصفحة الرئيسية', 'en' => 'Home page'],
                'tabs' => [
                    'services' => [
                        'label' => ['ar' => 'سكشن الخدمات', 'en' => 'Services section'],
                        'fields' => [
                            'what_we_do' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'services_title' => self::field('العنوان', 'Title'),
                            'services_intro' => self::field('الوصف', 'Description', 'textarea'),
                        ],
                    ],
                    'projects' => [
                        'label' => ['ar' => 'سكشن الأعمال', 'en' => 'Work section'],
                        'fields' => [
                            'selected_work' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'projects_title' => self::field('العنوان', 'Title'),
                            'projects_intro' => self::field('الوصف', 'Description', 'textarea'),
                        ],
                    ],
                    'introduction' => [
                        'label' => ['ar' => 'سكشن النبذة', 'en' => 'Introduction section'],
                        'fields' => [
                            'home_intro_media_caption' => self::field('النص فوق الصورة', 'Text over the image', 'textarea'),
                            'home_intro_label' => self::field('العنوان التمهيدي', 'Intro label'),
                            'home_intro_title' => self::field('العنوان الرئيسي', 'Main title', 'textarea'),
                            'home_intro_text' => self::field('الوصف', 'Description', 'textarea'),
                            'home_intro_link_text' => self::field('نص الرابط', 'Link text'),
                            'home_intro_founder_label' => self::field('وصف المؤسس', 'Founder label'),
                            'home_intro_signature' => self::field('التوقيع', 'Signature'),
                        ],
                    ],
                    'methodology' => [
                        'label' => ['ar' => 'سكشن منهجية العمل', 'en' => 'Process section'],
                        'fields' => [
                            'our_process' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'methodology_title' => self::field('العنوان', 'Title'),
                            'methodology_intro' => self::field('الوصف', 'Description', 'textarea'),
                        ],
                    ],
                    'testimonials' => [
                        'label' => ['ar' => 'سكشن آراء العملاء', 'en' => 'Testimonials section'],
                        'fields' => [
                            'client_words' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'testimonials_title' => self::field('العنوان', 'Title'),
                            'testimonials_intro' => self::field('الوصف', 'Description', 'textarea'),
                        ],
                    ],
                    'posts' => [
                        'label' => ['ar' => 'سكشن المدونة', 'en' => 'Blog section'],
                        'fields' => [
                            'insights' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'posts_title' => self::field('العنوان', 'Title'),
                            'posts_intro' => self::field('الوصف', 'Description', 'textarea'),
                        ],
                    ],
                ],
            ],
            'inner' => [
                'label' => ['ar' => 'الصفحات الداخلية والأخرى', 'en' => 'Inner and other pages'],
                'tabs' => [
                    'services' => [
                        'label' => ['ar' => 'صفحة الخدمات', 'en' => 'Services page'],
                        'fields' => [
                            'route_services.index' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'services_page_title' => self::field('العنوان', 'Title'),
                            'services_page_intro' => self::field('الوصف', 'Description', 'textarea'),
                        ],
                    ],
                    'service' => [
                        'label' => ['ar' => 'صفحة تفاصيل الخدمة', 'en' => 'Service details'],
                        'fields' => [
                            'included' => self::field('عنوان المميزات', 'Included heading'),
                            'packages' => self::field('عنوان الباقات', 'Packages heading'),
                            'pricing_request' => self::field('نص طلب السعر', 'Pricing request text'),
                            'service_related_title' => self::field('عنوان الأعمال المرتبطة', 'Related work heading'),
                        ],
                    ],
                    'projects' => [
                        'label' => ['ar' => 'صفحة الأعمال', 'en' => 'Work page'],
                        'fields' => [
                            'route_projects.index' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'projects_page_title' => self::field('العنوان', 'Title'),
                            'projects_page_intro' => self::field('الوصف', 'Description', 'textarea'),
                        ],
                    ],
                    'project' => [
                        'label' => ['ar' => 'صفحة تفاصيل العمل', 'en' => 'Project details'],
                        'fields' => [
                            'project_overview' => self::field('عنوان نبذة المشروع', 'Overview heading'),
                            'project_gallery' => self::field('عنوان معرض الصور', 'Gallery heading'),
                            'related_projects' => self::field('عنوان الأعمال المرتبطة', 'Related projects heading'),
                            'field_client' => self::field('مسمى العميل', 'Client label'),
                            'field_duration' => self::field('مسمى مدة التنفيذ', 'Duration label'),
                            'field_tech_stack' => self::field('مسمى التقنيات', 'Technology label'),
                            'visit_project' => self::field('نص زر زيارة المشروع', 'Visit project button'),
                            'want_similar' => self::field('نص الدعوة لمشروع مشابه', 'Similar project callout'),
                        ],
                    ],
                    'posts' => [
                        'label' => ['ar' => 'صفحة المدونة', 'en' => 'Blog page'],
                        'fields' => [
                            'route_posts.index' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'posts_page_title' => self::field('العنوان', 'Title'),
                            'posts_page_intro' => self::field('الوصف', 'Description', 'textarea'),
                            'post_related_title' => self::field('عنوان المقالات المرتبطة', 'Related posts heading'),
                        ],
                    ],
                    'methodology' => [
                        'label' => ['ar' => 'صفحة منهجية العمل', 'en' => 'Process page'],
                        'fields' => [
                            'route_methodology' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'methodology_page_title' => self::field('العنوان', 'Title'),
                            'methodology_page_intro' => self::field('الوصف', 'Description', 'textarea'),
                        ],
                    ],
                    'testimonials' => [
                        'label' => ['ar' => 'صفحة آراء العملاء', 'en' => 'Testimonials page'],
                        'fields' => [
                            'route_testimonials' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'testimonials_page_title' => self::field('العنوان', 'Title'),
                            'testimonials_page_intro' => self::field('الوصف', 'Description', 'textarea'),
                        ],
                    ],
                    'contact' => [
                        'label' => ['ar' => 'صفحة التواصل والنموذج', 'en' => 'Contact page and form'],
                        'fields' => [
                            'route_contact' => self::field('النص الصغير أعلى العنوان', 'Eyebrow text'),
                            'contact_title' => self::field('العنوان', 'Title'),
                            'contact_intro' => self::field('الوصف', 'Description', 'textarea'),
                            'name' => self::field('حقل الاسم', 'Name field'),
                            'email' => self::field('حقل البريد الإلكتروني', 'Email field'),
                            'phone' => self::field('حقل الهاتف', 'Phone field'),
                            'service' => self::field('حقل الخدمة', 'Service field'),
                            'message' => self::field('حقل الرسالة', 'Message field'),
                            'choose_service' => self::field('اختيار الخدمة', 'Choose service'),
                            'privacy_consent' => self::field('نص الموافقة على الخصوصية', 'Privacy consent', 'textarea'),
                            'send' => self::field('زر الإرسال', 'Send button'),
                            'sending' => self::field('نص الإرسال الجاري', 'Sending text'),
                            'quote_success' => self::field('رسالة نجاح الطلب', 'Success message', 'textarea'),
                            'request_received' => self::field('عنوان نجاح الطلب', 'Success title'),
                            'too_many' => self::field('رسالة كثرة المحاولات', 'Rate limit message', 'textarea'),
                        ],
                    ],
                    'pages' => [
                        'label' => ['ar' => 'من نحن والصفحات المضافة', 'en' => 'About and custom pages'],
                        'fields' => [
                            'route_about' => self::field('اسم صفحة من نحن', 'About page label'),
                            'about' => self::field('النص الصغير لصفحة من نحن', 'About eyebrow'),
                            'pages' => self::field('النص الصغير للصفحات', 'Pages eyebrow'),
                            'files' => self::field('عنوان الملفات', 'Files heading'),
                            'download' => self::field('زر تحميل الملف', 'Download button'),
                        ],
                    ],
                    'shared' => [
                        'label' => ['ar' => 'العناصر المشتركة', 'en' => 'Shared elements'],
                        'fields' => [
                            'home' => self::field('الرئيسية', 'Home'),
                            'request_quote' => self::field('زر ابدأ مشروعك', 'Start a project button'),
                            'view_all' => self::field('عرض الكل', 'View all'),
                            'explore_service' => self::field('اكتشف الخدمة', 'Explore service'),
                            'view_project' => self::field('عرض العمل', 'View project'),
                            'read_more' => self::field('اقرأ المزيد', 'Read more'),
                            'all' => self::field('الكل', 'All'),
                            'search' => self::field('البحث', 'Search'),
                            'no_records' => self::field('لا توجد نتائج', 'No records'),
                            'load_more' => self::field('زر تحميل المزيد', 'Load more button'),
                            'loading_more' => self::field('نص تحميل المزيد', 'Loading more text'),
                            'preview' => self::field('المعاينة', 'Preview'),
                            'previous' => self::field('السابق', 'Previous'),
                            'next' => self::field('التالي', 'Next'),
                            'play' => self::field('تشغيل', 'Play'),
                            'pause' => self::field('إيقاف مؤقت', 'Pause'),
                            'close' => self::field('إغلاق', 'Close'),
                            'menu' => self::field('القائمة', 'Menu'),
                            'toggle_theme' => self::field('تغيير المظهر', 'Toggle theme'),
                            'whatsapp' => self::field('واتساب', 'WhatsApp'),
                        ],
                    ],
                    'footer' => [
                        'label' => ['ar' => 'الفوتر', 'en' => 'Footer'],
                        'fields' => [
                            'footer_links' => self::field('عنوان الروابط', 'Links heading'),
                            'footer_contact' => self::field('عنوان التواصل', 'Contact heading'),
                            'footer_follow' => self::field('عنوان السوشيال ميديا', 'Social heading'),
                            'services' => self::field('عنوان الخدمات', 'Services heading'),
                            'copyright' => self::field('نص حقوق النشر', 'Copyright text'),
                        ],
                    ],
                    'privacy_errors' => [
                        'label' => ['ar' => 'الخصوصية وصفحة الخطأ', 'en' => 'Privacy and error page'],
                        'fields' => [
                            'tracking_consent' => self::field('رسالة ملفات الارتباط', 'Cookie consent message', 'textarea'),
                            'accept' => self::field('زر الموافقة', 'Accept button'),
                            'decline' => self::field('زر الرفض', 'Decline button'),
                            'cookie_settings' => self::field('إعدادات ملفات الارتباط', 'Cookie settings'),
                            'not_found' => self::field('عنوان صفحة غير موجودة', 'Not found title'),
                            'not_found_text' => self::field('وصف صفحة غير موجودة', 'Not found description', 'textarea'),
                            'back' => self::field('رجوع', 'Back'),
                        ],
                    ],
                ],
            ],
        ];
    }

    /** @return array{ar: string, en: string, type?: string} */
    private static function field(string $arabic, string $english, string $type = 'text'): array
    {
        return ['ar' => $arabic, 'en' => $english, 'type' => $type];
    }

    /** @return array<int, string> */
    public static function keys(): array
    {
        $keys = [];

        foreach (self::groups() as $group) {
            foreach ($group['tabs'] as $tab) {
                array_push($keys, ...array_keys($tab['fields']));
            }
        }

        return array_values(array_unique($keys));
    }

    public static function stateKey(string $translationKey): string
    {
        return str_replace('.', '__dot__', $translationKey);
    }
}
