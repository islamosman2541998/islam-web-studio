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
                'title' => ['translated', 'عنوان الترحيب', 'Welcome title', ['ar' => 'أهلًا بيك في الاستوديو', 'en' => 'Welcome to the studio']], 'description' => ['translated', 'وصف الترحيب', 'Welcome description', ['ar' => 'كل تفاصيل شغلك، في مكان واحد.', 'en' => 'Every detail of your work, in one place.']],
                'background' => ['color', 'لون الطبقة فوق الخلفية', 'Background overlay color', '#0A3323'],
                'background_overlay_opacity' => ['opacity', 'شفافية اللون فوق الخلفية', 'Background overlay opacity', 79],
                'background_image' => ['asset', 'صورة الخلفية', 'Background image', null],
                'card_background' => ['color', 'لون كارت تسجيل الدخول', 'Login card color', '#FFFDF4'],
                'card_opacity' => ['opacity', 'شفافية كارت تسجيل الدخول', 'Login card opacity', 100],
                'logo' => ['asset', 'اللوجو', 'Logo', null], 'show_logo' => ['bool', 'إظهار اللوجو', 'Show logo', true], 'show_description' => ['bool', 'إظهار الوصف', 'Show description', true], 'show_remember' => ['bool', 'إظهار تذكرني', 'Show remember me', true], 'show_site_link' => ['bool', 'إظهار العودة للموقع', 'Show back to website', true],
            ],
            'preloader' => [
                'enabled' => ['bool', 'تشغيل شاشة التحميل', 'Enable preloader', true], 'transitions' => ['bool', 'الانتقالات بين الصفحات', 'Page transitions', true], 'logo' => ['asset', 'لوجو التحميل', 'Preloader logo', null], 'animation' => ['animation', 'نوع الحركة', 'Animation', 'fade'], 'duration' => ['duration', 'مدة الحركة بالمللي ثانية', 'Animation duration (ms)', 400],
            ],
            'seo' => [
                'title' => ['translated', 'العنوان الافتراضي', 'Default title', ['ar' => 'إسلام ويب ستوديو | تطوير وتصميم وتسويق', 'en' => 'Islam Web Studio | Development, design & marketing']],
                'description' => ['translated', 'وصف نتائج البحث', 'Meta description', ['ar' => 'نطور مواقع ومتاجر وأنظمة Laravel، ونصمم هوية ومحتوى وحملات تساعد مشروعك ينمو.', 'en' => 'Websites, commerce, Laravel systems, thoughtful design and campaigns that help your business grow.']],
                'keywords' => ['translated', 'الكلمات المفتاحية', 'Keywords', ['ar' => 'برمجة مواقع، Laravel، متاجر، تسويق', 'en' => 'Laravel, web development, e-commerce, digital studio']], 'og_image' => ['asset', 'صورة المشاركة الافتراضية', 'Default sharing image', null], 'index' => ['bool', 'السماح بفهرسة الموقع عند النشر', 'Allow indexing when deployed', true],
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
                'intro_media_caption' => ['translated', 'النص الصغير فوق الصورة', 'Text over the image', ['ar' => "WE CONNECT\nTHE DOTS.", 'en' => "WE CONNECT\nTHE DOTS."]],
                'intro_media_caption_color' => ['color', 'لون النص فوق الصورة', 'Image caption color', '#0A3323'],
                'intro_label' => ['translated', 'العنوان التمهيدي', 'Intro label', ['ar' => 'من أول فكرة، لآخر تفصيلة', 'en' => 'From first thought to final detail']],
                'intro_title' => ['translated', 'عنوان لماذا نحن', 'Why us title', ['ar' => "شغل متكامل.\nبطابع يشبهك.", 'en' => "One studio.\nYour whole story."]],
                'intro_text' => ['translated', 'وصف لماذا نحن', 'Why us text', ['ar' => 'نجمع التطوير والتصميم والتسويق في رحلة واحدة واضحة. نفهم مشروعك، نبني المناسب له، ونفضل معاك بعد الإطلاق.', 'en' => 'Development, design and marketing in one clear journey. We understand your business, build what fits, and stay with you after launch.']],
                'intro_link_enabled' => ['bool', 'إظهار رابط القسم', 'Show section link', true],
                'intro_link_text' => ['translated', 'نص رابط القسم', 'Section link text', ['ar' => 'من نحن', 'en' => 'About']],
                'intro_link_url' => ['url', 'رابط القسم — يقبل {locale}', 'Section URL — accepts {locale}', '/{locale}/about'],
                'intro_link_new_tab' => ['bool', 'فتح الرابط في نافذة جديدة', 'Open link in a new tab', false],
                'intro_founder_label' => ['translated', 'وصف المؤسس', 'Founder label', ['ar' => 'إسلام — المؤسس والمطور الرئيسي', 'en' => 'Islam — Founder & technical lead']],
                'intro_signature' => ['translated', 'التوقيع', 'Signature', ['ar' => 'Islam.', 'en' => 'Islam.']],
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
