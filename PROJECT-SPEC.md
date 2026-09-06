# Islam Web Studio — Complete Build Prompt (PRD)

A precise, end-to-end brief to build **Islam Web Studio**: a bilingual (Arabic/English) agency website plus a powerful admin dashboard. Build it exactly as specified. Every module below lists its **database columns**, **dashboard logic**, and **frontend rendering**. Work in the phase order in Section 13.

---

## 1. Overview & goals

- A large website for the brand **Islam Web Studio** (a full-service digital studio) with a powerful admin dashboard that controls everything.
- **Voice:** studio ("we"), with Islam as founder/technical lead.
- **Goals (equally weighted):** generate leads AND showcase work.
- **Value proposition:** one partner from idea → build → marketing (development + content + design + paid ads).
- **Bilingual:** Arabic + English across the entire site AND dashboard, with automatic RTL/LTR.

---

## 2. Brand identity

**Logo:** the provided "Islam Web Studio — Digital Solutions" mark (Arabic-calligraphic WS monogram). Use the transparent/vector version for navbar, footer, preloader, and favicon. Provide light and dark variants.

**Color palette (exact):**

| Role | Name | Hex |
|---|---|---|
| Primary brand / dark surfaces / headings | Dark Green | `#0A3323` |
| Secondary / links / buttons | Midnight Green (Teal) | `#105666` |
| Accent / highlights | Moss Green | `#839958` |
| Warm accent / CTAs | Rosy Brown | `#D3968C` |
| Light background / light surfaces | Beige | `#F7F4D5` |

**Light mode:** background Beige `#F7F4D5` (with white section variants), primary text Dark Green `#0A3323`, primary action Midnight Green `#105666`, CTA Rosy Brown `#D3968C`, accent Moss Green `#839958`.

**Dark mode:** background Dark Green `#0A3323` (deeper shade for canvas), surfaces a step lighter, primary text Beige `#F7F4D5`, accents Moss Green / Rosy Brown, links/buttons a lightened teal.

All five hues are stored as CSS variables/design tokens and are also editable from dashboard **Design settings** (so theme colors and dark/light can be tuned without code).

**Typography (bilingual, editorial feel):**
- Arabic: a refined Arabic typeface (e.g. IBM Plex Sans Arabic or Almarai) with two weights (regular + medium/bold).
- Latin: an editorial pairing — a characterful display for headings (e.g. a grotesque/serif) + a clean sans for body. Avoid default system-generic pairings.
- Consistent type scale, generous line-height, sentence case.

---

## 3. Design direction — chic & professional, NOT "AI-look"

The design must feel like an intentional, human-crafted studio brand — not a generic AI/template aesthetic.

- **Avoid:** purple/blue neon gradients, glow effects, glassmorphism overload, generic hero blobs, over-rounded everything, stocky "techy" clichés, rainbow color cycling.
- **Do:** flat, refined surfaces; a strong editorial grid; deliberate whitespace; restrained, tasteful motion; real project mockups/photography; earthy brand palette used with discipline (one accent per section); crisp typography with clear hierarchy.
- Micro-interactions are subtle and purposeful (hover states, smooth reveals on scroll), never flashy.
- Aim for a premium boutique-studio feel: calm, confident, and polished.

---

## 4. Tech stack & packages

- Backend: **Laravel** (latest), MySQL.
- Admin panel: **Filament**.
- Dynamic interactivity/tables: **Livewire + Alpine.js**.
- Frontend: **Blade + Tailwind** (RTL/LTR).
- Slider: **Swiper.js**.
- Packages: `spatie/laravel-translatable` (i18n content), `spatie/laravel-medialibrary` + `intervention/image` (media + image processing), `spatie/laravel-permission` (roles), `maatwebsite/excel` (exports), `spatie/laravel-sitemap` (sitemap).
- Build: **Vite** (minify, code-splitting, tree-shaking).

---

## 5. Global / cross-cutting requirements (apply everywhere)

### 5.1 Bilingual (AR/EN)
- Translatable fields stored as JSON. Every admin form has **Arabic tab / English tab** for translatable fields.
- Language switcher on site and dashboard; direction flips RTL/LTR automatically.
- Static UI strings via `lang/ar` + `lang/en` + a translations manager in settings.

### 5.2 Responsive — mobile-first (top priority)
- Every page, component, and table works and looks correct on mobile first, then tablet, then desktop.
- Touch targets ≥ 44px; hamburger menu (driven by Menu Builder); Swiper touch/drag.
- **Dashboard tables collapse to card view on mobile**; filters move into a drawer/modal.
- Test from 320px up to large desktop.

### 5.3 Navbar behavior (required)
- **Home page:** navbar is **transparent, overlaid on the hero slider**. Logo/link colors are the light-on-dark variant while transparent.
- **On scroll (Home):** after the first small scroll, the navbar becomes **solid + sticky** (fixed to top) with a subtle border/shadow, smoothly transitioning background and text colors.
- **All other pages:** navbar is **solid + sticky from the start**.
- Transition is smooth (background + color), and respects reduced-motion.

### 5.4 Base Livewire table (built once, inherited by all resources)
- Live search, per-column filters, **date range (from/to) filter**, sortable columns, pagination.
- **Inline toggles without reload:** publish/unpublish status and feature (featured) toggles.
- Bulk actions; **Excel export of the filtered result**.
- Mobile → card layout.

### 5.5 Media pipeline (auto image processing)
- On upload: compress + convert to **WebP/AVIF** + generate **multiple sizes (srcset)** + thumbnail + **blur placeholder (LQIP)**.
- Store width/height to prevent layout shift.

### 5.6 SEO trait (any SEO-able entity)
- Fields (AR/EN): `meta_title`, `meta_description`, `og_image`, `keywords`, `canonical`, `index/noindex`.
- Global defaults fill blanks; `hreflang` for both locales; Schema.org output.

### 5.7 Preloader & page transitions
- Logo preloader on first load with a tasteful animation; smooth transition between pages so navigation feels SPA-like (no white flash). Controlled from settings; respects `prefers-reduced-motion`. (Full detail in Section 10.)

### 5.8 Roles & permissions
- Users with roles (Admin/Editor/…) and per-module permissions; optional activity log.

---

## 6. Data model overview

`MenuLocation`, `MenuItem`, `Page`, `PageMedia`, `Service`, `ServiceCategory`, `Project`, `ProjectCategory`, `ProjectMedia`, `ProjectMetric`, `Slider`, `Slide`, `Post`, `PostCategory`, `Tag`, `Testimonial`, `MethodologyStep`, `Lead`, `Media`, `Setting`, `Translation`, `User`, `Role`, `Permission`.

Conventions: translatable text fields are JSON; every content model has `slug`, `status` (draft/published), `sort_order`, `is_featured` where relevant, timestamps, and SEO fields where SEO-able.

---

## 7. Modules — detailed (columns · dashboard logic · frontend)

> For every resource: Filament CRUD with AR/EN tabs, the Base Livewire table (5.4), soft deletes, and validation.

### 7.1 Settings
- **Storage:** key/value `settings` table (grouped) or a settings singleton per group.
- **Groups:** General (site name AR/EN, logo light/dark, favicon, contact, WhatsApp, socials, footer text); **Design** (five brand colors, dark/light toggle, fonts); **Login page** (background image/color, logo, welcome title/text AR/EN, element visibility); **Preloader** (logo choice, animation type, duration, on/off); **SEO defaults**; **Scripts** (GA, Meta Pixel, TikTok, custom head/body); **Translations manager**.
- **Frontend:** consumed globally (cached, cache cleared on save).

### 7.2 Localization / translations
- Translatable content via JSON columns; UI strings via lang files.
- **Dashboard:** a translations manager to edit static strings AR/EN.

### 7.3 Media library
- `media` (via medialibrary): file, type, sizes, conversions, alt (AR/EN), dimensions.
- **Dashboard:** browse/search/filter by type; reused by any field.
- Auto-processing per 5.5.

### 7.4 Users & roles
- `users`: name, email, password, role, avatar, is_active.
- `roles`, `permissions` (spatie).
- **Dashboard:** manage users, assign roles; per-module permission gates.

### 7.5 Menu Builder
- `menu_locations`: key (navbar/footer/mobile), name.
- `menu_items`: `menu_location_id`, `parent_id` (for dropdowns), `title` (JSON AR/EN), `type` (route|external|page|dynamic_group), `route_name` (nullable), `url` (nullable), `page_id` (nullable), `dynamic_source` (nullable: services|projects|…), `icon`, `target_blank`, `visibility` (all|auth), `is_active`, `sort_order`.
- **Dashboard logic:** builder UI with **drag-and-drop ordering** and parent/child nesting; item type switches which field is required; a `dynamic_group` item lets you pick a source and specific items (e.g. a "Services" item that auto-lists selected services as a dropdown). Same pattern reusable for Projects or any collection.
- **Frontend:** rendered navbar/footer/mobile; dynamic groups resolve live; changes appear immediately (menu cache cleared on save).

### 7.6 Pages (Page Builder)
- `pages`: `title` (JSON), `slug`, `excerpt` (JSON), `hero_type` (image|video), `hero_media_id`, `content` (JSON, rich text), `status`, SEO fields.
- `page_media`: `page_id`, `media_id`, `kind` (gallery_image|file), `caption` (JSON), `sort_order`.
- **Dashboard:** create page with title/description, hero image or video, rich content, attach image/file galleries; publish → becomes selectable as a "page" link in Menu Builder.
- **Frontend:** `/{slug}` template renders hero + content + gallery + SEO.

### 7.7 Services
- `services`: `name` (JSON), `slug`, `short_description` (JSON), `long_description` (JSON), `icon`, `image_media_id`, `service_category_id`, `pricing_type` (packages|on_request), `is_featured`, `is_active`, `sort_order`, SEO fields.
- `service_features`: `service_id`, `text` (JSON), `sort_order`. (Or a JSON repeater column.)
- `service_packages` (if pricing_type=packages): `service_id`, `name` (JSON), `price`, `features` (JSON), `sort_order`.
- `service_categories`: `name` (JSON), `slug`, `sort_order`.
- **Dashboard:** CRUD with features repeater and optional packages; inline featured/status toggles; Excel export.
- **Frontend:** services grid on home + a detail page per service; feeds the "Services" dropdown in the menu.

### 7.8 Portfolio (Projects) — full spec
- `projects`: `title` (JSON), `slug`, `project_category_id`, `client`, `duration`, `overview` (JSON), `challenge` (JSON), `solution` (JSON), `result` (JSON), `main_media_type` (image|video), `main_media_id`, `main_media_poster_id` (video poster), `main_media_mobile_id` (optional mobile image/video), `live_url`, `services` (JSON multi or pivot), `tech_stack` (JSON tags), `testimonial_id` (linked review), `is_featured`, `is_active`, `sort_order`, SEO fields.
- `project_media` (gallery): `project_id`, `media_id`, `type` (image|video), `caption` (JSON), `sort_order`.
- `project_metrics`: `project_id`, `label` (JSON), `value` (e.g. "+40%"), `sort_order`.
- `project_categories`: `name` (JSON), `slug`, `sort_order`.
- **Dashboard logic:** CRUD with main media (image/video + poster + optional mobile version), gallery repeater, metrics repeater, services (multi), tech tags, live URL, linked testimonial, featured (pinned first); Base table with category filter, date range, search, Excel export, inline toggles.
- **Frontend — card:** cover (image or video poster) + category badge + title + one bold result metric + a preview icon that opens the gallery in a **lightbox**; the card body links to the project page. Video card: poster + short muted preview on hover/tap (no sound).
- **Frontend — detail page (in order):** Hero (main media, uses mobile version on small screens) → facts bar (client / duration / services / tech / metrics) → overview → Case Study (challenge → solution → result) → **gallery Swiper** (images + videos, thumbnails, arrows, drag, lightbox) → linked client testimonial → "Visit project" button → CTA ("want a result like this?") → related projects (same category).

### 7.9 Sliders (Swiper)
- `sliders`: `name`, `autoplay` (bool), `autoplay_speed`, `draggable`, `arrows`, `dots`, `loop`, `is_active`. The display location is fixed internally to the Home Hero and is not shown as an admin field. Activating a group deactivates the previous group.
- `slides`: `slider_id`, `desktop_media_type` (image|video), `desktop_media_id`, **`mobile_media_type` (image|video), `mobile_media_id`** (separate mobile media, shown on small screens), `title` (JSON), `description` (JSON), `button_text` (JSON), `button_url`, `button_color`, `sort_order`, `is_active`.
- **Dashboard:** manage slider groups and slides with drag-sort and inline toggles; per-slide desktop and mobile media.
- **Frontend:** one full-screen (`100svh`) Home Hero immediately below the transparent navbar; image/video covers the viewport, with title, description and CTA over it. Content aligns to the right in Arabic and left in English. Swiper supports autoplay, drag, arrows, dots and separate mobile media. Inner pages use a solid navbar and never render this slider.

### 7.10 Blog
- `posts`: `title` (JSON), `slug`, `excerpt` (JSON), `content` (JSON rich), `featured_media_id`, `post_category_id`, `author_id`, `status`, `published_at`, `is_featured`, SEO + Article schema.
- `post_categories`: `name` (JSON), `slug`. `tags` + `post_tag` pivot.
- **Dashboard:** CRUD with scheduled publish, category/author/status filters, date range, search, inline publish toggle, Excel export.
- **Frontend:** archive + single post + categories; "latest tip" block on home.

### 7.11 Testimonials
- `testimonials`: `client_name`, `client_company`, `avatar_media_id`, `quote` (JSON), `rating`, `is_approved`, `is_featured`, `project_id` (nullable link), `sort_order`.
- **Dashboard:** approval-before-publish, filters by status/rating, inline approve toggle.
- **Frontend:** slider on home + dedicated page + shown on the linked project's page.

### 7.12 Methodology
- `methodology_steps`: `title` (JSON), `description` (JSON), `icon`, `number`, `sort_order`, `is_active`.
- **Dashboard:** drag-sort, status toggle.
- **Frontend:** "our methodology" section (discover → plan → build → launch → follow-up).

### 7.13 Leads / CRM-lite
- `leads`: `name`, `phone`, `email`, `service_id` (nullable), `message`, `source` (form|whatsapp|…), `status` (new|contacted|quoted|won|lost), `internal_notes`, `created_at`.
- **Dashboard:** pipeline view; Base table with **date range filter**, status/service/source filters, search, **inline status change (move in pipeline, no reload)**, Excel export.
- **Frontend:** quick quote form + direct WhatsApp button feeding this module.

---

## 8. Frontend pages (site)

- **Home:** hero **Swiper** (with transparent navbar overlay) → services overview → featured projects (with metrics) → why us → methodology → testimonials slider → latest post → final CTA + quote form/WhatsApp.
- **Services:** list + detail per service.
- **Portfolio:** filterable grid + detail page (Section 7.8).
- **About (Who we are):** studio story + skills + tools.
- **Methodology:** the process steps.
- **Blog:** archive + single + categories.
- **Testimonials:** dedicated page.
- **Contact / request a quote:** form + direct WhatsApp.
- **Custom pages:** from the Page Builder.
- Global: navbar (7.5 behavior), footer (dynamic), language switcher, dark/light switcher, preloader, page transitions.

---

## 9. Performance (mandatory)

- **Images/video:** auto compress + WebP/AVIF + responsive srcset per device; `loading="lazy"` + `decoding="async"`; fixed width/height (no CLS); LQIP blur placeholder; video poster + `preload=metadata` + lighter mobile version + no autoplay with sound.
- **Assets/code:** Vite minify + tree-shake + code-splitting; defer/async JS; critical CSS inline, rest deferred; load Swiper etc. only where needed.
- **Server/data:** config/route/view cache; cache repeated queries (menus, settings) with auto-clear on save; avoid N+1 (eager loading); indexes on filtered columns; always paginate; HTTP caching headers; Gzip/Brotli; CDN-ready assets.
- **Targets:** LCP < 2.5s, CLS < 0.1, INP < 200ms; Lighthouse 90+ (Perf/SEO/Best-practices/Accessibility).

---

## 10. Preloader & page transitions

- **First load:** a preloader screen with the **logo animated tastefully** (pulse / draw-in / fade) until the page is ready, then it fades out smoothly.
- **Between pages:** a short, smooth logo/transition overlay on navigation (no white flash) — near-SPA feel. Implement with a light overlay + Alpine/Livewire transitions (optionally enhanced by the View Transitions API where supported, with a graceful fallback).
- **Settings-controlled:** which logo, animation type, duration, on/off.
- **Performance-aware:** GPU-friendly CSS animation, respects `prefers-reduced-motion`, never blocks content loading.

---

## 11. Responsive (detail)

- Mobile-first styles at every breakpoint (mobile/tablet/desktop).
- Comfortable touch targets, hamburger menu, touch slider.
- Dashboard tables → cards on mobile; filters in a drawer/modal.
- Per-device images (srcset/art-direction) + **mobile slider media**.
- Verified 320px → large desktop.

---

## 12. SEO

- Per-entity SEO fields + global defaults; **sitemap.xml** auto; robots; Schema.org (Article/Service/Organization/Breadcrumb); **hreflang** for AR/EN; clean URLs; canonical; OG/Twitter cards.

---

## 13. Build phases

**Phase 1 — Foundations**
Laravel + Filament + Livewire + Tailwind (RTL/LTR); design tokens from the brand palette + dark/light; typography; translations (content + UI); Base Livewire table; media pipeline (compression/WebP/srcset/LQIP); roles & permissions; **preloader + page transitions**; **navbar behavior**; Settings (General/Design/Login/Preloader/SEO/Scripts).

**Phase 2 — Core content**
Menu Builder (with dynamic groups); Pages (Page Builder); Services (+ categories, features, packages); Portfolio (full: main/mobile media, gallery, metrics, testimonial link) with grid + detail page.

**Phase 3 — Presentation**
Sliders (with mobile media) + home hero; Testimonials (approval + project link); Methodology; Blog (categories, tags, scheduling).

**Phase 4 — Conversion & polish**
Leads/CRM + quote form + WhatsApp; Excel export + full search/filters/date-range on all tables; complete SEO (sitemap/schema/hreflang/meta); performance pass to hit Core Web Vitals; QA on mobile.

---

## 14. Definition of done

- Every page/component is responsive and verified on mobile.
- All content is dashboard-managed and renders in AR/EN with correct direction.
- Every table has search, per-column filters, date range, Excel export, and inline status/feature toggles without reload.
- Images are compressed, converted to WebP/AVIF, served with srcset + lazy load + LQIP; no CLS.
- Preloader and smooth page transitions work; navbar is transparent-over-hero on Home and solid+sticky elsewhere/on scroll.
- Lighthouse 90+ and Core Web Vitals within targets.
- Full SEO (sitemap/schema/hreflang/meta) on all entities.
- Design reads as a crafted, professional studio brand using the exact palette — not a generic AI/template look.
