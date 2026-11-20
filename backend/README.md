# NAMTECH application (Laravel + Inertia + React)

The NAMTECH website and CMS: this Laravel application plus the React front end in `../frontend`, joined by
Inertia. Controllers return Inertia pages (a React page component in `../frontend/src/pages` and its props);
there is no separate API. All routes live in
`routes/web.php`. See `../README.md` for the full stack and how a page is served.

- Public pages are server-rendered by Inertia SSR (Node.js) and hydrated in the browser.
- The CMS (`/admin/…`) uses Laravel's session and Fortify for sign-in, password reset and two-factor
  authentication; its screens render in the browser.
- Data: MySQL. Cache, sessions and queues use the database driver.


---

## 1. Stack and versions

| Area | Package | Version |
|---|---|---|
| Runtime | PHP | 8.5 |
| | MySQL | 8.0+ |
| Framework | laravel/framework | 13.38 |
| | inertiajs/inertia-laravel (page bridge, SSR) | 3.5 |
| | laravel/fortify (login, password reset, 2FA) | 1.41 |
| | spatie/laravel-permission (roles) | 8.3 |
| | ezyang/htmlpurifier (rich-text sanitising) | 4.19 |
| | intervention/image (image variants, GD driver) | 4.4 |
| | predis/predis | 3.7 |

| Front end | react, react-dom | 19.3 |
| | @inertiajs/react, @inertiajs/vite | 3.9 |
| | bootstrap (Sass) | 5.3 |
| | @tiptap/react (CMS rich-text editor) | 3.32 |
| Build | vite, laravel-vite-plugin | 8.3, 3.2 |

PHP extensions: `pdo_mysql`, `gd` (with WebP and AVIF support), `fileinfo`, `intl`, `mbstring`, `openssl`. Node.js 22+.

## 2. Local setup

```bash
composer install
(cd ../frontend && npm install)
cp .env.example .env   # set DB_*, APP_URL (e.g. http://localhost:8000), mail settings
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan serve --port=8000   # and, in a second terminal: cd ../frontend && npm run dev
```

Queue worker: `php artisan queue:work --queue=mail,media,default`. During development the Vite dev server
also server-renders pages, so no separate SSR process is needed.

### Seeded accounts

`php artisan migrate --seed` creates the first Super Admin from `SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD` in `.env`
(a random password is generated and printed if none is set). In the `local` environment it also creates
`publisher@namtech.test` and `editor@namtech.test` with the same password. Never use these accounts in production.

### Tests

```bash
php artisan test
```

Tests make Inertia requests, the way the browser does, so pages answer with `{component, props}` JSON.
`$response->assertPage(fn ($page) => $page->component('site/Page')->where('sections.0.type', 'x'))`
checks a page; form submissions redirect with flash data or validation errors (`assertSessionHasErrors`).
They run on the MySQL test database `namtechweb_test` (see `phpunit.xml`).

## 3. Architecture

```
app/
  Http/Controllers/Site/       public pages, search, forms, sitemap and robots
  Http/Controllers/Admin/      CMS: generic ResourceController, media, menus, leads, users, settings
  Http/Responses/PageResponse  renders an Inertia page with its SEO data (the "seo" prop)
  Http/Middleware/             HandleInertiaRequests (shared props: site chrome, CMS user and sidebar, flash),
                               HandleRedirects (CMS 301s), CachePublicPages (anonymous responses, server-rendered
                               HTML only), SecurityHeaders, EnsureAdminAccess
  Admin/Resources/             declarative CMS definitions (fields, list columns) per content type
  Sections/                    SectionRegistry (approved page components + schemas) and SectionPresenter
  Services/                    PublicRenderer, ContentWorkflow (draft/review/schedule/publish), MediaUploader
  Models/Concerns/             HasPublication, HasSeo, HasSections, TracksMediaUsage, CreatesSlugRedirects
  Support/                     AdminSession and SiteChrome (shared props), SiteSettings, ContentCache…
routes/web.php                 every route
resources/views/app.blade.php  Inertia root view (links ../frontend's Vite build)
../frontend/src/               React: app.jsx, ssr.jsx, pages/ (Inertia pages), components/, hooks/, styles/
```

### Content model

| Area | Tables |
|---|---|
| Access | `users`, `roles`, `permissions`, `model_has_roles`, `role_has_permissions` |
| Pages | `pages` (nested, unique full `path`), `page_sections` (type + validated JSON), `content_revisions` |
| Navigation | `menus` (by location), `menu_items` (nested, link to page or URL), `announcements` |
| Academics | `schools`, `centres`, `programs` |
| Faculty | `faculty`, `faculty_program` |
| Partnerships | `partners`, `partnership_categories` |
| Editorial | `posts` (news / blog / press), `categories`, `tags`, `post_tag`, `authors` |
| Media | `media`, `media_usages` |
| Campus | `galleries`, `gallery_items` |
| Mission | `initiatives`, `statistics` (only rows marked approved are shown) |
| Leads | `enquiries`, `newsletter_subscribers` |
| Platform | `settings`, `redirects`, `audit_logs`, `jobs`, `failed_jobs`, `cache`, `sessions` |

Structured, searchable content uses relational columns. JSON is used only for page-section configuration (validated against the section schema on save) and a few repeating fields (faculty qualifications, gallery items in revisions).

Slugs are unique per URL scope (pages per parent, posts per type). When a live item's URL changes, a 301 redirect is created automatically; chains are collapsed and loops removed.

### Publishing workflow

- **Draft** content is edited directly. **Live** content is never changed by an edit: changes are saved as *pending changes* (a working revision) until a Publisher publishes them.
- Editors can **submit for review**; Publishers and Super Admins can **publish**, **schedule** (also for pending changes of live content) and **unpublish**.
- Every save/publish records a revision (last 50 kept); any revision can be restored – restoring is treated as a new edit, so live content still goes through publishing.
- **Preview** renders drafts and pending changes in the real public templates (signed-in users only, `noindex`).
- `php artisan content:publish-scheduled` runs every minute from the scheduler.

### Controlled section builder

Pages are built from approved components only (`app/Sections/SectionRegistry.php`), e.g. homepage hero, alternating feature blocks, schools/centres/programs tabs, partners showcase, faculty showcase, labs carousel, campus showcase, mission band, news carousel, page hero, intro, rich text, image and text, icon card grid, statistics band, image card carousel, tabs, FAQ, steps, people, programs list, latest posts, logo strip, testimonials, gallery, video, documents, call-to-action band and enquiry form. Editors can add, reorder, hide, duplicate and remove sections and edit their fields, but cannot change layout or styling.

Rich text supports headings, paragraphs, lists, quotes, links and YouTube/Vimeo embeds. HTML is sanitised server-side with an allow-list on every save; links are validated (site paths, `https://`, `mailto:`, `tel:` only).

### Roles and permissions

| Permission | Super Admin | Publisher | Editor |
|---|---|---|---|
| Create and edit content, upload media, submit for review | ✓ | ✓ | ✓ |
| Publish, schedule, unpublish, delete content | ✓ | ✓ | |
| Announcement strip, approved statistics, mission initiatives | ✓ | ✓ | |
| Menus, redirects, view enquiries and subscribers | ✓ | ✓ | |
| Export enquiries / subscribers (CSV) | ✓ | | |
| Users, global settings, audit log | ✓ | | |

Permissions are checked server-side on every action. There is no public registration: Super Admins invite users, who set their own password via an emailed link.

## 4. Security

- Fortify authentication under `/admin`: login throttling (5/min per email+IP, 30/hour per IP), password reset, two-factor authentication (TOTP + recovery codes). Two-factor is **required** for Super Admins and Publishers when `ADMIN_ENFORCE_2FA=true` (default in production). Inactive accounts cannot sign in and are signed out when deactivated.
- Sessions: encrypted, `HttpOnly`, `SameSite=Lax`, `Secure` in production (`SESSION_SECURE_COOKIE=true`). CSRF protection via Laravel's XSRF cookie.
- Security headers on every response: `X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`, HSTS on HTTPS, and a Content Security Policy in production. Admin responses are `no-store` and `noindex`.
- Uploads: content-sniffed (not the browser's MIME type), JPEG/PNG/WebP/AVIF only, size and pixel limits, random names, original kept on a private disk; only re-encoded variants are public. SVG is not accepted. The Nginx config denies script execution under `/storage`.
- Forms: server-side validation, honeypot + minimum completion time, per-IP throttling, consent text stored with each submission. CSV exports are permission-controlled, audited and escape spreadsheet formulas.
- Eloquent / query builder parameter binding throughout; the React front end escapes output; the only raw HTML it renders is rich text sanitised here.
- Audit log of sign-ins (including failures), content changes, publishing, media, menus, settings, users and exports.
- Secrets live only in `.env` (git-ignored). `.env.example` contains no secrets.

## 5. Production deployment

`deploy/` in the project root builds the Bluehost package (PHP 8.3); see the root `README.md`.

Shared hosting runs PHP 8.3 and MySQL with no background processes, so the package sets `QUEUE_CONNECTION=sync`,
`CACHE_STORE=database`, `SESSION_DRIVER=database` and `INERTIA_SSR_ENABLED=false` (pages are rendered in the browser).
The settings live in `backend/.env` inside the package, built from `deploy/bluehost.env`. Scheduled publishing needs a
cPanel cron job running `php artisan schedule:run` every minute (see `deploy/bluehost/README.md`).

### Transactional email

Set `MAIL_MAILER` to a transactional provider (SMTP, or a Laravel mail transport such as Postmark, Amazon SES, Mailgun or Resend) with `MAIL_FROM_ADDRESS` on the `namtech.ac` domain. Before launch, configure domain authentication for the sending domain: **SPF** (include the provider), **DKIM** (provider-supplied keys) and **DMARC** (start with `p=none` and monitoring, then tighten). Delivery to inboxes cannot be guaranteed; failures are retried (1 min, 5 min, 15 min, 1 h) and recorded on the enquiry, which is always saved first and can be re-sent from the CMS.

### Backups and restore

Back up the MySQL database (e.g. `mysqldump --single-transaction`) and `storage/app` (uploaded media) nightly,
keep copies off-site, and test a restore into a separate database regularly.

## 6. Operations

- `php artisan content:launch-check` – lists placeholder copy, empty sections, unpublished legal pages, unconfigured settings, hidden menu items, images without alt text and unapproved statistics.
- `php artisan content:publish-scheduled` – publishes due scheduled content (runs every minute via the scheduler).
- Failed jobs: `php artisan queue:failed`, `php artisan queue:retry all`.
- Logs: `storage/logs/laravel.log`; release problems are in `storage/logs/deploy.log`.

## 7. Not included

Student management, application processing and staff/student portals are **not** part of this system. Portal and application links are configurable (menu items, program "Application link", settings). Visitor analytics is not built in; add the institution's chosen analytics tool and update the Content Security Policy and privacy policy accordingly.
