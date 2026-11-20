# NAMTECH Web Project

The NAMTECH public website and CMS: one Laravel application with a React user interface,
connected by Inertia. Controllers return React page components and their props; there is no
separate API.

## Technology stack

| Layer | Technology | Role |
|---|---|---|
| Server runtime | PHP 8.5 · Laravel 13.38 | Routing, controllers, models, validation, queues, scheduler |
| Page bridge | Inertia 3 (Laravel + React) | Controllers return page components and props, no separate API |
| User interface | React 19 · Bootstrap 5.3 (Sass) | Public templates and CMS screens with NAMTECH tokens |
| Server rendering | Inertia SSR on Node.js 22+ | Finished HTML for SEO and a fast first paint |
| Build | Vite 8 · laravel-vite-plugin | Client and SSR bundles, split per page |
| Rich text | Tiptap 3 · HTML Purifier | CMS editor; allow-list sanitising on every save |
| Access | Laravel Fortify · spatie/permission | Login, password reset, two-factor; three roles |
| Images | Intervention Image (GD) | AVIF, WebP and JPEG variants in several widths |
| Data | MySQL 8 | Content store; cache, sessions, queues |

```
NAMTechWebProject/
├── frontend/                       React user interface (built with Vite)
│   ├── src/
│   │   ├── app.jsx                 browser entry (hydrates server-rendered pages)
│   │   ├── ssr.jsx                 Inertia SSR entry (Node.js)
│   │   ├── pages/                  Inertia page components: site/… (Page, programs/, faculty/,
│   │   │                           posts/…), admin/… (Dashboard, resources/, media/, menus/, auth/…), Error
│   │   ├── components/             site/ (layout, sections, cards), admin/ (form engine, section
│   │   │                           builder, rich-text editor, media picker), Head, Seo, Carousel…
│   │   ├── hooks/                  usePage, useForm
│   │   ├── router/                 Inertia router and Link (full page load between site and CMS)
│   │   ├── lib/                    motion, embeds, links, JSON fetch helper for CMS widgets
│   │   ├── images/
│   │   └── styles/                 site.scss, admin.scss (Bootstrap 5.3 with NAMTECH tokens)
│   ├── vite.config.js              builds into ../backend (public/build and bootstrap/ssr)
│   └── package.json
│
├── backend/                        Laravel application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/        Site/ (public pages, forms, search, sitemap) and Admin/ (CMS)
│   │   │   ├── Middleware/         HandleInertiaRequests (shared props), security headers,
│   │   │   │                       page cache, CMS redirects, admin access
│   │   │   └── Responses/          PageResponse: renders an Inertia page with its SEO data
│   │   ├── Models/  Services/  Sections/  Admin/Resources/  Support/  Jobs/
│   ├── resources/views/            app.blade.php (Inertia root view), mail/
│   ├── public/                     index.php, images, favicon; build/ is written by the front end
│   ├── routes/web.php              all routes
│   └── composer.json
```

## How a page is served

1. A request reaches a Laravel controller, which returns `PageResponse::render('site/programs/Show', $props, $seo)`,
   an Inertia page.
2. `HandleInertiaRequests` adds shared props: `site` (settings, announcement strip, menus) on public pages,
   `auth` and `navigation` on CMS screens, and one-time `flash` messages.
3. On a first visit, Laravel asks the Inertia SSR server for the finished HTML and returns it with
   `resources/views/app.blade.php`. Later visits are Inertia requests that return only the page JSON.
4. Forms submit with Inertia; controllers redirect with a flash message or validation errors, which
   arrive in `usePage().props.flash` and `useForm().errors`.

CMS screens (`/admin`) render in the browser only; CMS previews of drafts use the public templates.
The public site and the CMS use different style sheets, so moving between them loads a fresh page.

## Run locally

Requirements: PHP 8.5, Composer, MySQL 8, Node.js 22+.

```bash
cd frontend && npm install && cd ..
cd backend
composer install
cp .env.example .env          # set DB_*, SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD
php artisan key:generate
php artisan storage:link
php artisan migrate --seed    # creates the database content and the first admin
```

Then run these in two terminals:

```bash
cd backend && php artisan serve --port=8000
```

```bash
cd frontend && npm run dev
```

Open http://localhost:8000. During development the Vite dev server also server-renders pages
(through `@inertiajs/vite`), so no separate SSR process is needed.

Queue worker for emails and image variants: `cd backend && php artisan queue:work --queue=mail,media,default`.

Production build: `cd frontend && npm run build` (client and SSR bundles, written into `backend/`),
then `cd backend && php artisan inertia:start-ssr`. The SSR bundle includes its packages, so the
server does not need `node_modules` in `backend/`.

## Tests

```bash
cd backend && php artisan test
```

47 feature tests make Inertia requests the way the browser does and cover public pages, the
publishing workflow, permissions, preview, scheduling, revisions, redirects, sanitising, page
caching, forms, CSV export and the media library. They run on a MySQL test database
(`namtechweb_test`, see `phpunit.xml`).

## Deploy

Production runs on Bluehost shared hosting. Build the all-in-one package on a Mac with PHP 8.3 from Homebrew:

```bash
bash deploy/build.sh
```

This writes `namtech-bluehost.zip` with the compiled front end, PHP packages, the settings file, a fresh database
export and the uploaded media. Upload it to `public_html` in cPanel File Manager and extract it; the first visit
applies the release. Setup and update steps are in `deploy/bluehost/README.md`.
