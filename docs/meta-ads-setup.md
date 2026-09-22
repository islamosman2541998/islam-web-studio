# إعداد ربط Meta Lead Ads

تم تجهيز المشروع لاستقبال الليدز من نماذج Meta تلقائياً، وحفظ بيانات الحملة ومجموعة الإعلانات والإعلان، ومزامنة أداء الإعلانات مع الداشبورد.

## 1. متغيرات البيئة

أضف القيم التالية في ملف `.env` على السيرفر. لا ترسل `META_APP_SECRET` أو `META_ACCESS_TOKEN` في الرسائل أو تحفظهما في Git.

```env
META_GRAPH_VERSION=v26.0
META_APP_ID=
META_APP_SECRET=
META_ACCESS_TOKEN=
META_PAGE_ACCESS_TOKEN=
META_WEBHOOK_VERIFY_TOKEN=
META_AD_ACCOUNT_ID=
META_PAGE_ID=
META_AD_CURRENCY=EGP
```

- استخدم رقم الحساب الإعلاني في `META_AD_ACCOUNT_ID`، سواء مع `act_` أو بدونه.
- أنشئ قيمة عشوائية طويلة لـ `META_WEBHOOK_VERIFY_TOKEN` واستخدم القيمة نفسها داخل إعداد Webhook في Meta.
- يفضّل استخدام System User access token طويل المدة مع صلاحيات الأصول المطلوبة بدلاً من توكن مستخدم قصير المدة.
- استخدم `META_ACCESS_TOKEN` لقراءة الحساب الإعلاني، و`META_PAGE_ACCESS_TOKEN` الخاص بالصفحة لاستقبال وقراءة بيانات الليدز. إذا تُرك توكن الصفحة فارغاً سيحاول المشروع استخدام التوكن العام للتوافق مع الإعدادات القديمة.

## 2. إعداد تطبيق Meta

1. أنشئ تطبيقاً أو استخدم تطبيق Business موجوداً داخل Meta for Developers.
2. اربط التطبيق بالـ Business Portfolio والصفحة والحساب الإعلاني الصحيحين.
3. امنح توكن الإعلانات صلاحية `ads_read`، وامنح مستخدم النظام/الصفحة صلاحية الوصول للصفحة و`leads_retrieval` مع `pages_show_list` و`pages_read_engagement` والصلاحيات التي يطلبها إعداد التطبيق.
4. أضف عنوان الاستقبال التالي إلى Webhooks:

```text
https://islam-web-studio.com/webhooks/meta/lead-ads
```

5. اختر Page واشترك في حقل `leadgen`، واستخدم قيمة `META_WEBHOOK_VERIFY_TOKEN` عند التحقق.

## 3. تشغيل المشروع

بعد حفظ متغيرات البيئة شغّل:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan meta-ads:sync --days=30
```

يجب تشغيل Laravel Scheduler كل دقيقة على الاستضافة:

```cron
* * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

ويجب تشغيل Queue Worker باستمرار حتى تُحفظ الليدز فور وصول Webhook:

```bash
php artisan queue:work --tries=4
```

استخدم Supervisor أو خدمة الاستضافة لإبقاء Queue Worker شغالاً.

## 4. اختبار الربط

1. استخدم أداة Lead Ads Testing Tool في Meta لإرسال Lead تجريبي.
2. تأكد أن الليد ظهر في قسم متابعة الطلبات داخل الداشبورد ومصدره Meta.
3. من الصفحة الرئيسية للداشبورد اضغط مزامنة إعلانات Meta، أو شغّل أمر المزامنة.
4. تأكد من ظهور الإنفاق والليدز وتكلفة الليد والوصول والضغطات وجدول أداء كل إعلان.

الويب هوك يتحقق من توقيع Meta، وتخزين الليد idempotent حتى لا يتكرر عند إعادة إرسال الحدث. المزامنة الدورية تحدّث صفوف الأداء اليومية بدلاً من تكرارها.
