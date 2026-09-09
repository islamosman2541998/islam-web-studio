<?php

namespace App\Support;

class SettingsRegistry
{
    public static function groups(): array
    {
        return [
            'general' => [
                'site_name' => ['translated', 'اسم الموقع', 'Site name', ['ar' => 'إسلام ويب ستوديو', 'en' => 'Islam Web Studio']],
                'tagline' => ['translated', 'وصف مختصر', 'Tagline', ['ar' => 'شريكك من الفكرة إلى التأثير', 'en' => 'Your partner, from idea to impact']],
                'footer_text' => ['translated', 'نص الفوتر', 'Footer text', ['ar' => 'نصمم، نطور، ونساعد فكرتك تكبر.', 'en' => 'We design, develop, and help your idea grow.']],
                'logo_light' => ['asset', 'لوجو الخلفية الفاتحة', 'Logo on light surfaces', null], 'logo_dark' => ['asset', 'لوجو الخلفية الداكنة', 'Logo on dark surfaces', null], 'favicon' => ['asset', 'أيقونة الموقع', 'Favicon', null],
                'email' => ['email', 'البريد الإلكتروني', 'Contact email', 'islamwebstudio@info.com'], 'phone' => ['text', 'التليفون', 'Phone', '01114292011'], 'whatsapp' => ['phone', 'واتساب بصيغة دولية بدون +', 'WhatsApp international digits', ''],
                'address' => ['translated', 'العنوان', 'Location', ['ar' => 'مصر · نعمل معك أينما كنت', 'en' => 'Based in Egypt · Working everywhere']],
                'socials' => ['socials', 'روابط التواصل', 'Social links', [
                    ['label' => 'Facebook', 'url' => 'https://www.facebook.com/share/1EYAYWY39M/?mibextid=wwXIfr'],
                    ['label' => 'Instagram', 'url' => 'https://www.instagram.com/islam_web_studio?stkn=MWtmNzJ0Zmx1emNoaQ=='],
                    ['label' => 'TikTok', 'url' => 'https://www.tiktok.com/@islam_webstudio?_r=1&_t=ZS-99VEy7MBLyG'],
                ]],
            ],
            'design' => [
                'primary' => ['color', 'الأخضر الرئيسي', 'Dark green', '#0A3323'], 'secondary' => ['color', 'البترولي', 'Teal', '#105666'], 'accent' => ['color', 'الزيتوني', 'Moss green', '#839958'], 'warm' => ['color', 'الوردي', 'Rose', '#D3968C'], 'beige' => ['color', 'البيج', 'Beige', '#F7F4D5'],
                'light_background' => ['color', 'خلفية الوضع النهاري', 'Light background', '#F7F4D5'], 'light_surface' => ['color', 'أسطح الوضع النهاري', 'Light surface', '#FFFDF4'], 'light_text' => ['color', 'نص الوضع النهاري', 'Light text', '#0A3323'],
                'dark_background' => ['color', 'خلفية الوضع الليلي', 'Dark background', '#071F17'], 'dark_surface' => ['color', 'أسطح الوضع الليلي', 'Dark surface', '#0A3323'], 'dark_text' => ['color', 'نص الوضع الليلي', 'Dark text', '#F7F4D5'], 'dark_link' => ['color', 'روابط الوضع الليلي', 'Dark links', '#A9D3C8'],
                'theme_switch' => ['bool', 'إظهار زر تغيير الوضع', 'Show theme switch', true], 'default_theme' => ['theme', 'الوضع الافتراضي', 'Default theme', 'light'], 'arabic_font' => ['arabic_font', 'الخط العربي', 'Arabic font', 'IBM Plex Sans Arabic'], 'english_font' => ['english_font', 'خط العناوين الإنجليزية', 'English display font', 'Cormorant Garamond'],
            ],
            'dashboard' => [
                'primary' => ['color', 'اللون الرئيسي للداش بورد', 'Admin primary', '#105666'], 'light_background' => ['color', 'خلفية نهارية', 'Light canvas', '#F7F4D5'], 'light_surface' => ['color', 'أسطح نهارية', 'Light panels', '#FFFDF4'], 'light_text' => ['color', 'نص نهاري', 'Light text', '#0A3323'], 'dark_background' => ['color', 'خلفية ليلية', 'Dark canvas', '#071F17'], 'dark_surface' => ['color', 'أسطح ليلية', 'Dark panels', '#0A3323'], 'dark_text' => ['color', 'نص ليلي', 'Dark text', '#F7F4D5'],
                'small_text_weight' => ['font_weight', 'سمك النصوص الصغيرة', 'Small text weight', '600'], 'small_text_size' => ['font_size', 'حجم النصوص الصغيرة', 'Small text size', '13'],
                'compact_sidebar' => ['bool', 'إمكانية طي القائمة', 'Collapsible sidebar', true],
            ],
            'login' => [
                'title' => ['translated', 'عنوان الترحيب', 'Welcome title', ['ar' => 'أهلًا بيك في الاستوديو', 'en' => 'Welcome to the studio']],
                'title_text_color' => ['color', 'لون عنوان الترحيب', 'Welcome title color', '#18181B'],
                'title_text_size' => ['font_px', 'حجم عنوان الترحيب', 'Welcome title size', 24],
                'description' => ['translated', 'وصف الترحيب', 'Welcome description', ['ar' => 'كل تفاصيل شغلك، في مكان واحد.', 'en' => 'Every detail of your work, in one place.']],
                'description_text_color' => ['color', 'لون وصف الترحيب', 'Welcome description color', '#71717A'],
                'description_text_size' => ['font_px', 'حجم وصف الترحيب', 'Welcome description size', 14],
                'label_text_color' => ['color', 'لون مسميات الحقول وتذكرني', 'Field labels and remember me color', '#18181B'],
                'label_text_size' => ['font_px', 'حجم مسميات الحقول وتذكرني', 'Field labels and remember me size', 14],
                'input_text_color' => ['color', 'لون النص داخل الحقول', 'Input text color', '#18181B'],
                'input_text_size' => ['font_px', 'حجم النص داخل الحقول', 'Input text size', 14],
                'button_text_color' => ['color', 'لون نص زر الدخول', 'Login button text color', '#FFFFFF'],
                'button_text_size' => ['font_px', 'حجم نص زر الدخول', 'Login button text size', 14],
                'link_text_color' => ['color', 'لون روابط الكارت', 'Card links color', '#0A3323'],
                'link_text_size' => ['font_px', 'حجم روابط الكارت', 'Card links size', 13],
                'footer_text_color' => ['color', 'لون النص أسفل الكارت', 'Card footer text color', '#6B7A74'],
                'footer_text_size' => ['font_px', 'حجم النص أسفل الكارت', 'Card footer text size', 11],
                'background' => ['color', 'لون الطبقة فوق الخلفية', 'Background overlay color', '#0A3323'],
                'background_overlay_opacity' => ['opacity', 'شفافية اللون فوق الخلفية', 'Background overlay opacity', 79],
                'background_image' => ['asset', 'صورة الخلفية', 'Background image', null],
                'card_background' => ['color', 'لون كارت تسجيل الدخول', 'Login card color', '#FFFDF4'],
                'card_opacity' => ['opacity', 'شفافية كارت تسجيل الدخول', 'Login card opacity', 100],
                'logo' => ['asset', 'اللوجو', 'Logo', null], 'show_logo' => ['bool', 'إظهار اللوجو', 'Show logo', true], 'show_description' => ['bool', 'إظهار الوصف', 'Show description', true], 'show_remember' => ['bool', 'إظهار تذكرني', 'Show remember me', true], 'show_site_link' => ['bool', 'إظهار العودة للموقع', 'Show back to website', true],
            ],
            'preloader' => [
                'enabled' => ['bool', 'تشغيل شاشة التحميل عند فتح الموقع', 'Show preloader when the site opens', true],
                'transitions' => ['bool', 'إظهارها أثناء الانتقال بين الصفحات', 'Show during page transitions', true],
                'show_logo' => ['bool', 'إظهار اللوجو', 'Show logo', true],
                'logo' => ['asset', 'لوجو شاشة التحميل', 'Preloader logo', null],
                'logo_width' => ['preloader_size', 'عرض اللوجو', 'Logo width', 84],
                'show_text' => ['bool', 'إظهار النص أسفل اللوجو', 'Show text below logo', false],
                'text' => ['translated', 'نص شاشة التحميل', 'Preloader text', ['ar' => 'بنجهّز لك التجربة', 'en' => 'Preparing your experience']],
                'text_color' => ['color', 'لون النص', 'Text color', '#F7F4D5'],
                'text_size' => ['font_px', 'حجم النص', 'Text size', 13],
                'background_type' => ['preloader_background', 'نوع الخلفية', 'Background type', 'color'],
                'background_color' => ['color', 'لون الخلفية', 'Background color', '#0A3323'],
                'background_opacity' => ['opacity', 'شفافية لون الخلفية', 'Background color opacity', 100],
                'background_image' => ['asset', 'صورة الخلفية', 'Background image', null],
                'background_image_opacity' => ['opacity', 'شفافية صورة الخلفية', 'Background image opacity', 100],
                'background_fit' => ['media_fit', 'طريقة عرض صورة الخلفية', 'Background image fit', 'cover'],
                'overlay_color' => ['color', 'لون الطبقة فوق الصورة', 'Image overlay color', '#0A3323'],
                'overlay_opacity' => ['opacity', 'شفافية الطبقة فوق الصورة', 'Image overlay opacity', 30],
                'panel_enabled' => ['bool', 'إظهار كارت خلف اللوجو', 'Show panel behind logo', false],
                'panel_color' => ['color', 'لون كارت اللوجو', 'Logo panel color', '#071F17'],
                'panel_opacity' => ['opacity', 'شفافية كارت اللوجو', 'Logo panel opacity', 45],
                'panel_radius' => ['radius', 'استدارة زوايا الكارت', 'Panel corner radius', 24],
                'show_progress' => ['bool', 'إظهار مؤشر التحميل', 'Show loading indicator', true],
                'progress_color' => ['color', 'لون مؤشر التحميل', 'Loading indicator color', '#D3968C'],
                'animation' => ['animation', 'طريقة حركة اللوجو', 'Logo animation', 'pulse'],
                'minimum_duration' => ['wait_duration', 'أقل مدة للظهور بالمللي ثانية', 'Minimum display time (ms)', 850],
                'duration' => ['duration', 'مدة الاختفاء بالمللي ثانية', 'Exit duration (ms)', 400],
            ],
            'seo' => [
                'title' => ['translated', 'عنوان الموقع في نتائج جوجل', 'Site title in Google results', ['ar' => 'إسلام ويب ستوديو | تطوير مواقع ومتاجر وأنظمة', 'en' => 'Islam Web Studio | Websites, commerce & Laravel systems']],
                'description' => ['translated', 'وصف الموقع في نتائج جوجل', 'Site description in Google results', ['ar' => 'إسلام ويب ستوديو لتطوير المواقع والمتاجر الإلكترونية وأنظمة Laravel، مع خدمات التصميم والمحتوى والتسويق الرقمي لنمو مشروعك.', 'en' => 'Islam Web Studio builds websites, e-commerce stores and Laravel systems, with design, content and digital marketing services for growing businesses.']],
                'keywords' => ['translated', 'الكلمات المفتاحية الأساسية', 'Primary keywords', ['ar' => 'تطوير مواقع، تصميم مواقع، متاجر إلكترونية، أنظمة Laravel، تسويق رقمي، إسلام ويب ستوديو', 'en' => 'web development, web design, e-commerce, Laravel systems, digital marketing, Islam Web Studio']],
                'logo' => ['asset', 'لوجو النشاط لمحركات البحث', 'Organization logo for search engines', null],
                'og_image' => ['asset', 'صورة المشاركة الافتراضية', 'Default social sharing image', null],
                'index' => ['bool', 'السماح لجوجل ومحركات البحث بفهرسة الموقع', 'Allow search engines to index the site', true],
            ],
            'scripts' => [
                'enabled' => ['bool', 'تفعيل التتبع بعد موافقة الزائر', 'Enable consent-based tracking', false], 'ga_id' => ['ga', 'معرّف Google Analytics', 'Google Analytics ID', ''], 'meta_id' => ['digits', 'معرّف Meta Pixel', 'Meta Pixel ID', ''], 'tiktok_id' => ['pixel', 'معرّف TikTok Pixel', 'TikTok Pixel ID', ''], 'head' => ['code', 'أكواد Head — للمالك فقط', 'Head code — owner only', ''], 'body' => ['code', 'أكواد Body — للمالك فقط', 'Body code — owner only', ''],
            ],
            'home' => [
                'intro_enabled' => ['bool', 'إظهار قسم نبذة الرئيسية', 'Show home introduction section', true],
                'intro_media_enabled' => ['bool', 'إظهار جزء الصورة', 'Show image panel', true],
                'intro_media' => ['asset', 'صورة قسم النبذة', 'Introduction image', null],
                'intro_media_fit' => ['media_fit', 'طريقة عرض الصورة', 'Image fit', 'cover'],
                'intro_frame_enabled' => ['bool', 'إظهار الإطار الزخرفي فوق الصورة', 'Show decorative image frame', true],
                'intro_media_background' => ['color', 'لون خلفية الصورة', 'Image background color', '#839958'],
                'intro_media_caption_color' => ['color', 'لون النص فوق الصورة', 'Image caption color', '#0A3323'],
                'intro_link_enabled' => ['bool', 'إظهار رابط القسم', 'Show section link', true],
                'intro_link_url' => ['url', 'رابط القسم — يقبل {locale}', 'Section URL — accepts {locale}', '/{locale}/about'],
                'intro_link_new_tab' => ['bool', 'فتح الرابط في نافذة جديدة', 'Open link in a new tab', false],
            ],
        ];
    }

    public static function defaults(): array
    {
        $all = [];
        foreach (self::groups() as $group => $fields) {
            foreach ($fields as $key => $d) {
                $all[$group.'.'.$key] = $d[3];
            }
        }

        return $all;
    }
}
