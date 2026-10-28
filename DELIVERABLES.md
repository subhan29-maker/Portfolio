# Subhan — Developer Portfolio: Deliverables

Delivered: **one deployable Laravel application** — the public portfolio **and** its private
admin dashboard — consolidated from the old standalone *Portfolio CRM* into this repository.
One codebase, one SQLite database, one domain; no second project, no microservice, and no
HTTP calls between the public site and its own admin.

Stack: **Laravel 13 · PHP 8.4 · SQLite · Blade · Vite · vanilla CSS/JS** (no Tailwind, no SPA).

---

## 1. Summary

**Public site (URLs and design unchanged):** Home `/`, About `/about`, Skills `/skills`,
Projects `/projects` + detail pages `/projects/{slug}`, Contact `/contact`, custom 404.
Dark navy/charcoal theme with a blue/violet accent, `clamp()` type, self-hosted fonts,
hero `@keyframes` entrance, IntersectionObserver scroll reveals, looping tech ticker,
skip-link/landmarks/heading order, zero horizontal scroll at 320–1440px. Nothing about
the visitor experience changed: no admin chrome on public pages, no login prompts,
no dummy content, all original content and images preserved.

**Admin dashboard:** `/login` → `/admin/dashboard`, everything under `/admin/*` guarded by
`auth` + `admin` middleware and never linked from public pages.

- **Dashboard** — totals (total/published/draft/unpublished/featured/demo), recent items,
  quick actions, sidebar + top nav, responsive off-canvas drawer, empty/loading/success/
  validation/error states as reusable Blade components.
- **Project CRUD** — title, unique slug (auto-generated), summary, detailed description,
  tech stack, cover image, optional gallery (per-image delete), live/GitHub URLs, featured
  flag, demo flag, display order; `draft` / `published` / `unpublished` states with
  publish & unpublish shortcuts; search + status/category filters + pagination;
  server-side upload and URL validation; confirmation dialogs on every destructive action.
  Only published projects reach the public site; drafts/unpublished 404 publicly.
- **Profile & settings** `/admin/profile` — one form for the picture (upload with live
  preview, type/size/content validation, unique filenames on the `public` disk, old file
  deleted only after the new settings saved, reset-to-default with confirmation and
  physical cleanup) and the identity fields (name, role, hero label/headline/lead,
  availability, e-mail, phone, location, GitHub/LinkedIn/X URLs, picture description).
- **Security** — generic login error for every failure case, session regeneration on
  login/logout, CSRF everywhere, `throttle:10,1` on login, contact-specific rate limiter,
  server-side validation on all inputs, no public registration, no hardcoded credentials.
  Admin pages carry `<meta name="robots" content="noindex, nofollow">`.

**Data:** existing portfolio rows preserved (additive migrations only — the original
`contact_messages` table and its rows are untouched), the old CRM's real projects, images
and admin account imported verbatim via a read-only, idempotent command.

---

## 2. Exact routes

### Public
| Method | URI | Name | Notes |
|---|---|---|---|
| GET | `/` | `home` | |
| GET | `/about` | `about` | |
| GET | `/skills` | `skills` | |
| GET | `/projects` | `projects.index` | |
| GET | `/projects/{slug}` | `projects.show` | published only; drafts/unknown → 404 |
| GET | `/contact` | `contact` | |
| POST | `/contact` | `contact.store` | `throttle:contact` (3/min, 25/day per IP) |

### Authentication
| Method | URI | Name | Notes |
|---|---|---|---|
| GET | `/login` | `login` | guests only; signed-in admin → dashboard |
| POST | `/login` | `login.store` | `guest` + `throttle:10,1` |
| POST | `/logout` | `logout` | authenticated; invalidates session |

### Admin (`auth` + `admin` middleware, `admin.` name prefix)
| Method | URI | Name |
|---|---|---|
| GET | `/admin` | → redirect to `admin.dashboard` |
| GET | `/admin/dashboard` | `admin.dashboard` |
| GET | `/admin/profile` | `admin.profile.edit` |
| PUT | `/admin/profile` | `admin.profile.update` |
| DELETE | `/admin/profile/picture` | `admin.profile.picture.destroy` |
| GET | `/admin/projects` | `admin.projects.index` |
| GET | `/admin/projects/create` | `admin.projects.create` |
| POST | `/admin/projects` | `admin.projects.store` |
| GET | `/admin/projects/{project}` | `admin.projects.show` |
| GET | `/admin/projects/{project}/edit` | `admin.projects.edit` |
| PUT | `/admin/projects/{project}` | `admin.projects.update` |
| PATCH | `/admin/projects/{project}/publish` | `admin.projects.publish` |
| PATCH | `/admin/projects/{project}/unpublish` | `admin.projects.unpublish` |
| DELETE | `/admin/projects/{project}/gallery/{index}` | `admin.projects.gallery.destroy` |
| DELETE | `/admin/projects/{project}` | `admin.projects.destroy` |

Admin `{project}` uses implicit model binding (ID/slug); public show resolves the slug
directly through the repository. The old CRM REST API (`/api/v1/*`) does **not** exist in
this app — its two clients were deleted (see §7).

---

## 3. Files

### Created
| File | Purpose |
|---|---|
| `app/Models/Project.php`, `app/Enums/ProjectStatus.php` | Project record + `draft/published/unpublished` |
| `app/Models/Setting.php` | Key/value settings (profile picture path + alt text) |
| `app/Support/PortfolioSettings.php` | Layered config → database → uploaded-picture resolution for both halves |
| `app/Portfolio/ProjectRepository.php` | DB-backed read side for the public pages (published-only) |
| `app/Http/Controllers/Admin/{Dashboard,Project,Profile}Controller.php` | Dashboard, CRUD, picture+details |
| `app/Http/Controllers/Auth/LoginController.php` | Login/logout |
| `app/Http/Middleware/EnsureAdmin.php` | Admin-only gate (aliased `admin`) |
| `app/Http/Requests/{ProjectRequest,UpdateProfileRequest}.php` | Server-side validation |
| `app/Services/{ProjectMediaService,ProfileImageService}.php` | Cover/gallery & picture storage on the `public` disk |
| `app/Console/Commands/MakeAdminCommand.php` | `portfolio:make-admin` |
| `app/Console/Commands/ImportCrmDataCommand.php` | `portfolio:import-crm-data` |
| `database/migrations/2026_10_21_000000_create_projects_table.php` | Projects (additive) |
| `database/migrations/2026_10_21_000001_create_settings_table.php` | Settings (additive) |
| `database/migrations/2026_10_21_000002_add_is_admin_to_users_table.php` | `users.is_admin` column (additive) |
| `database/factories/ProjectFactory.php`, `UserFactory::admin()` | Test data |
| `database/seeders/ProjectsSeeder.php` | The 4 config starter projects (`db:seed`) |
| `resources/views/layouts/admin.blade.php`, `layouts/guest.blade.php` | Dashboard / guest shells |
| `resources/views/auth/login.blade.php` | Login page |
| `resources/views/admin/dashboard.blade.php`, `admin/projects/{index,create,edit,show,_form}.blade.php`, `admin/profile/edit.blade.php` | Dashboard screens |
| `resources/views/components/flash.blade.php`, `components/nav-link.blade.php`, `components/form/{input,select,textarea,file,checkbox}.blade.php` | Reusable UI + form components |
| `resources/views/pagination/default.blade.php` | Pagination view (`Paginator::defaultView`) |
| `resources/css/admin.css`, `resources/js/admin.js` | Dashboard styles/behaviour (built with `app.css`) |
| `tests/Feature/{Authentication,ProjectManagement,ProfileImage,ProfileDetails,ImportCrmDataCommand}Test.php` | Coverage for everything above |

### Modified
| File | Change |
|---|---|
| `routes/web.php` | Auth + admin routes; global `{project}` binding removed (public show takes a slug) |
| `bootstrap/app.php` | `admin` middleware alias, `redirectGuestsTo` → `/login`, `redirectUsersTo` → dashboard |
| `app/Providers/AppServiceProvider.php` | View composer shares `profile`/`navigation`/`technologies` to every view (profile memoized per request), contact + login rate limiters, pagination view |
| `app/Http/Controllers/ProjectController.php` | Slug resolution via repository; drafts/unknown → 404 |
| `app/Models/User.php` | `isAdmin()` |
| `database/factories/UserFactory.php`, `database/seeders/DatabaseSeeder.php` | `admin()` state; seeder = projects only (no user, no password) |
| `config/portfolio.php` | CRM block removed; `projects` = starter content only; identity values = defaults the dashboard overrides |
| `resources/views/layouts/app.blade.php` + `home/about/skills/projects/contact` views | Title/meta/identity read from shared `$profile` (incl. page meta descriptions) |
| `resources/css/components.css` | Badge modifiers `--ok/--warn/--danger/--accent/--muted` |
| `resources/views/components/icon.blade.php` | New icons (grid, plus, trash, eye, upload, check-circle, search…) |
| `vite.config.js` | `admin.css` / `admin.js` entries added |
| `.env.example` | All `PORTFOLIO_CRM_*` keys removed |

### Removed
`app/Portfolio/CrmProjectClient.php`, `app/Portfolio/PortfolioSettingsClient.php`,
`app/Console/Commands/ExportProjectsCommand.php`, their tests
(`CrmIntegrationTest`, `ExportProjectsCommandTest`, `PortfolioProfileImageTest`),
and every `PORTFOLIO_CRM_*` env key. The archive CRM was only read (opened in SQLite
query-only mode); `D:\Projects\portfolio-crm.rar` is untouched.

---

## 4. Setup & run (Windows / PowerShell)

```powershell
composer install                      # dependencies
Copy-Item .env.example .env           # only for a fresh clone
php artisan key:generate              # only for a fresh clone
php artisan migrate                   # 7 migrations, additive only
php artisan storage:link              # public\storage junction for uploads
node node_modules/vite/bin/vite.js build   # fonts + app.css + admin.css/js
php artisan serve                     # http://localhost:8000
```

`npm run dev` / `npm run build` also work where `npm.ps1` isn't blocked by execution
policy (this machine blocks it — hence the direct `node` command). Dependency-free
alternative (run from `public/`):

```powershell
cd public
php -S 127.0.0.1:8000 -t . ..\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php
```

> Browse via `http://localhost:8000`: media URLs are built from `APP_URL`
> (`http://localhost:8000` in `.env`), so `127.0.0.1` would mix hosts. Set `APP_URL`
> to the real domain before deploying.

Optional starter content (never wipes anything; creates no account):

```powershell
php artisan db:seed                    # the 4 starter projects, sort_order 10–40
```

## 5. Database: migrations, backup, restore

```powershell
php artisan migrate:status             # verify
```

| Migration | Effect |
|---|---|
| `2026_10_13_002502_create_contact_messages_table` | pre-existing |
| `2026_10_21_000000_create_projects_table` | **adds** `projects` (no existing table touched) |
| `2026_10_21_000001_create_settings_table` | **adds** `settings` |
| `2026_10_21_000002_add_is_admin_to_users_table` | **adds** one nullable column |

No `migrate:fresh`, `db:wipe`, `truncate` or reset was ever run; only additive
migrations, so all pre-existing rows (contact messages, users) survive.

```powershell
# BACKUP — ideally stop the server first (php artisan serve: Ctrl+C)
New-Item -ItemType Directory -Force backups | Out-Null
Copy-Item database\database.sqlite "backups\portfolio-$(Get-Date -Format yyyyMMdd-HHmm).sqlite"
Copy-Item storage\app\public "backups\storage-public-$(Get-Date -Format yyyyMMdd-HHmm)" -Recurse

# RESTORE — stop the server, then copy back over the live files
Copy-Item backups\portfolio-<stamp>.sqlite database\database.sqlite -Force
Copy-Item backups\storage-public-<stamp>\* storage\app\public -Recurse -Force
php artisan serve
```

Everything that matters lives in exactly two places: `database/database.sqlite` and
`storage/app/public` (served through `public/storage`). One pre-restoration copy exists
as `database/database.sqlite.backup-20261022`.

## 6. First administrator

There is no public registration and no seeded account — accounts only exist via CLI:

```powershell
# interactive: password is prompted privately (recommended for real deployments)
php artisan portfolio:make-admin you@example.com "Your Name"

# non-interactive: fine for local dev (the password lands in shell history)
php artisan portfolio:make-admin you@example.com "Your Name" --password="choose-a-long-password"
```

Then open `http://localhost:8000/login`. Re-running with the same e-mail updates that
account — this is also how you rotate a password (no password-change screen exists yet).

Current local accounts: **`admin@portfolio.test`** (created for verification; password
`Portfolio-Admin-2026!` — **change it**) and `admin@portfolio-crm.test` (imported with
its archived password hash verbatim, i.e. unknown — rotate it before use).

## 7. Importing the old CRM data

Read-only + idempotent: the source SQLite file is opened with `PRAGMA query_only=ON`,
never written to, and re-running changes nothing.

```powershell
php artisan portfolio:import-crm-data "C:\path\to\portfolio-crm" --dry-run   # preview
php artisan portfolio:import-crm-data "C:\path\to\portfolio-crm"             # apply
php artisan portfolio:import-crm-data "C:\path\to\portfolio-crm" --update    # force-overwrite
```

Keyed by slug: projects are created if missing and only updated when they still match the
starter content (or `--update` is passed), source IDs preserved when free, existing local
edits never silently overwritten. Cover/gallery images are copied from the archive's
`storage/app/public` into `storage/app/public/projects/{covers,gallery}`; admin accounts
come across with password hashes verbatim (`hashed` cast keeps bcrypt untouched).

Applied result on `database/database.sqlite`: **4 projects**
(`inventory-dashboard` 10, `thinkcode-crm` 20, `portfolio-crm` 30, `developer-portfolio` 40 —
the public order the site has always used), 2 of them carrying the real CRM content and
their 8 images, 2 accounts, 1 pre-existing contact message preserved, no duplicates.

## 8. Content management (what's editable where)

| Content | Where it's managed |
|---|---|
| Projects (all fields, states, media, order) | Dashboard → Projects |
| Name, role, hero label/headline/lead, availability, e-mail, phone, location, GitHub/LinkedIn/X, picture + picture description | Dashboard → Profile & settings |
| Skills groups, technologies ticker, navigation labels, starter project definitions | `config/portfolio.php` (defaults; **not** in the dashboard — agreed scope) |
| Site name, currency, notify e-mail, default picture path, default socials | `.env` → `config/portfolio.php` |

Saved dashboard values override the config defaults at runtime (stored in `settings`);
`PORTFOLIO_*` env values remain the fallbacks and the pre-save form values.

## 9. Tests

```powershell
php artisan test        # 96 tests, 469 assertions — all green (~31 s)
php vendor/bin/pint     # style clean
```

| File | Tests |
|---|---|
| `Feature/ProjectManagementTest` | 30 |
| `Feature/AuthenticationTest` | 14 |
| `Feature/ProfileImageTest` | 11 |
| `Feature/PortfolioPagesTest` | 10 |
| `Feature/ContactFormTest` | 10 |
| `Feature/ImportCrmDataCommandTest` | 9 |
| `Feature/ProfileDetailsTest` | 7 |
| `Unit/BudgetRangeTest` | 3 |
| `Feature/ExampleTest` + `Unit/ExampleTest` | 2 |

Tests run against **in-memory SQLite** (`phpunit.xml`: `DB_DATABASE=:memory:`), so they
can never touch real data. Also verified: `php artisan view:cache` compiles every view and
the Vite build emits `app.css`, `admin.css`, `admin.js`.

**Live verification performed** (PHP dev server + real database): all public pages 200 and
unbroken (incl. lazy-loaded storage images); unknown slug 404; draft created → hidden
publicly (list + 404) → visible in admin → deleted with confirmation dialog; login with a
wrong password shows only the generic error, correct login lands on the dashboard; counts
match the database exactly; profile picture upload (unique filename, live preview,
visible on the public homepage with its alt text) and reset-to-default (confirm, flash,
homepage reverts, file physically deleted); logout → `/login`; signed-out `/admin/dashboard`
redirects to `/login`; responsive drawer navigation; no admin/sign-in links anywhere on
public pages.

## 10. Remaining configuration (mail / env)

Contact messages save to the database with zero configuration. Optional pieces:

```env
PORTFOLIO_EMAIL=            PORTFOLIO_PHONE=           PORTFOLIO_LOCATION=
PORTFOLIO_GITHUB=           PORTFOLIO_LINKEDIN=        PORTFOLIO_X=
PORTFOLIO_AVAILABILITY="Available for new projects"
PORTFOLIO_PHOTO="images/profile/profile-placeholder.svg"   # default picture fallback
PORTFOLIO_NOTIFY_EMAIL=     # only then is the owner notified by mail
PORTFOLIO_CURRENCY="$"
```

```env
MAIL_MAILER=log             # smtp/mailpit/log — log writes to storage/logs/laravel.log
```

When `PORTFOLIO_NOTIFY_EMAIL` is unset no mail is attempted and the success message stays
"saved — I'll get back to you". E-mail/phone/location/socials are also editable in the
dashboard, which overrides these defaults at runtime.

## 11. Limitations & remaining tasks

- **Skills/technologies/navigation stay config-managed** — only the identity fields listed
  in §8 are dashboard-editable (agreed Phase 7 scope).
- **No password-change or user-management screen** — accounts (and password rotation) go
  through `portfolio:make-admin`; there is no list/delete-admin UI.
- **`admin@portfolio-crm.test`'s password is unknown** (imported hash) — rotate it via
  `portfolio:make-admin` if wanted, otherwise use your own account.
- **Change `admin@portfolio.test`'s password** (`Portfolio-Admin-2026!`) before this goes
  anywhere real.
- **No image resizing** — PHP has no GD/Imagick here, so uploads are validated by real
  MIME sniffing + size caps (5 MB profile picture; 4 MB per project image, cover plus up
  to 12 gallery images) and stored as-is.
- **Before deploying:** set `APP_URL`/`SERVER_PORT`, configure SMTP if mail is wanted,
  run `php artisan migrate --force`, `storage:link`, the Vite build, and create the first
  admin on the production shell.
- The CRM archive (`D:\Projects\portfolio-crm.rar`) and the extracted copy in the temp
  workspace were treated read-only and are byte-for-byte untouched.

## 12. Checks that could not be completed here

- **Real (non-reduced-motion) animation playback**: the test browser forces
  `prefers-reduced-motion: reduce`, so only that path was watched live; the animated path
  was verified structurally (transform/opacity keyframes, 0.5–0.85s, 34s ticker). Worth a
  30-second eyeball on a normal machine.
- **E-mail send end-to-end**: no mail credentials exist; DB save + success message verified
  live and in tests.
- **Lighthouse audit**: not available in this environment — structure, contrast,
  lazy-loading and assets were checked manually; a pass after deployment is recommended.
