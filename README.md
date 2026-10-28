# Subhan — Developer Portfolio (one application)

The public portfolio **and** its private admin dashboard live in this single Laravel app:
one codebase, one database, one domain. Visitors get the site; the owner signs in at
`/login` to manage projects, the profile picture and the identity details — no separate
CRM, no API calls between the two halves.

- **Public site**: Home `/`, About `/about`, Skills `/skills`, Projects `/projects`
  with detail pages `/projects/{slug}`, Contact `/contact`
- **Admin dashboard**: `/login` → `/admin/dashboard` — project CRUD with
  draft/published states, search/filters/pagination, media uploads, profile picture,
  and editable identity details (name, title, hero, contact, social links)

Stack: **Laravel 13 · PHP 8.4 · SQLite · Blade · Vite · vanilla CSS/JS** (no Tailwind,
no SPA). See `DELIVERABLES.md` for the full feature/route/file report.

---

## Requirements

- PHP ≥ 8.4 with the `pdo_sqlite` extension
- Composer
- Node.js (any recent version — the build is invoked directly through `node`)

## Setup (Windows / PowerShell)

```powershell
# 1. Dependencies
composer install

# 2. Environment (skip key:generate if .env already exists)
Copy-Item .env.example .env
php artisan key:generate

# 3. Database — runs the4 migrations (adds projects, settings, is_admin;
#    existing tables and rows are never touched: migrations are additive only)
php artisan migrate
php artisan migrate:status        # verify

# 4. Uploaded media must be reachable through public/storage
php artisan storage:link

# 5. Front-end assets (fonts + app.css + admin.css)
node node_modules/vite/bin/vite.js build

# 6. Run
php artisan serve                 # http://localhost:8000
```

`npm run dev` / `npm run build` also work if your shell allows `npm.ps1`
(this machine's execution policy blocks it, hence the direct `node` command).

A dependency-free alternative to `php artisan serve` (run from the `public` folder):

```powershell
cd public
php -S 127.0.0.1:8000 -t . ..\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php
```

> Use `http://localhost:8000` locally: media URLs are built from `APP_URL`
> (`http://localhost:8000` in `.env`), so `127.0.0.1` would mix hosts. Before deploying,
> set `APP_URL` to the real domain.

## Seed content (optional)

```powershell
php artisan db:seed        # writes the4 starter projects (config/portfolio.php)
```

`db:seed` only ever creates/updates the starter project rows. It creates **no user
account and no password**. Safe to re-run.

## First administrator

There is no public registration. Accounts are created from the CLI only:

```powershell
# interactive: prompts for the password privately (recommended)
php artisan portfolio:make-admin you@example.com "Your Name"

# non-interactive (fine for local dev; password ends up in your shell history)
php artisan portfolio:make-admin you@example.com "Your Name" --password="choose-a-long-password"
```

Re-running with the same e-mail updates that account (this is also how you rotate a
password — there is no password-change screen yet). Then open
`http://localhost:8000/login`.

The CRM data import (below) also carries over the archived admin
`admin@portfolio-crm.test` with its **original password hash verbatim** — since that
password is unknown, rotate it with `portfolio:make-admin` if you want to use the
address.

## Importing the old Portfolio CRM data

Read-only and idempotent: the source is opened with SQLite in query-only mode, nothing
is ever written back, and re-running the import changes nothing.

```powershell
# dry run first — lists exactly what would happen
php artisan portfolio:import-crm-data "C:\path\to\portfolio-crm" --dry-run

# apply (no --update flag needed: untouched starter rows are recognised and replaced)
php artisan portfolio:import-crm-data "C:\path\to\portfolio-crm"

# only if you deliberately edited rows here and still want the source to win:
php artisan portfolio:import-crm-data "C:\path\to\portfolio-crm" --update
```

Imports: projects (keyed by slug, source IDs preserved when free), their cover/gallery
images (copied into `storage/app/public/projects/`), and administrator accounts
(password hashes copied verbatim). Existing local edits are never overwritten unless
they still match the starter content (or you pass `--update`).

## Tests

```powershell
php artisan test
```

- **96 tests, 463 assertions — all green**
- Tests run against an **in-memory SQLite database** (`phpunit.xml` sets
  `DB_DATABASE=:memory:`), so they can never touch your real data
- Code style: `php vendor/bin/pint`

## Database backup & restore

```powershell
# backup — ideally stop the server first (php artisan serve: Ctrl+C)
New-Item -ItemType Directory -Force backups | Out-Null
Copy-Item database\database.sqlite "backups\portfolio-$(Get-Date -Format yyyyMMdd-HHmm).sqlite"
Copy-Item storage\app\public "backups\storage-public-$(Get-Date -Format yyyyMMdd-HHmm)" -Recurse

# restore — stop the server, then copy back over the live files
Copy-Item backups\portfolio-<stamp>.sqlite database\database.sqlite -Force
Copy-Item backups\storage-public-<stamp>\* storage\app\public -Recurse -Force
php artisan serve
```

The database file is `database/database.sqlite`; uploaded media lives under
`storage/app/public` (served through the `public/storage` link). Nothing outside those
two locations needs backing up.

## Where things live

| Path | What it holds |
|---|---|
| `config/portfolio.php` | Defaults for identity, navigation, skills, technologies and the starter projects — the admin dashboard overrides identity values at runtime |
| `app/Models/Project.php`, `app/Enums/ProjectStatus.php` | Project record + draft/published/unpublished states |
| `app/Support/PortfolioSettings.php` | Layered config → database → uploaded-picture resolution shared by both halves |
| `app/Http/Controllers/Admin/*` | Dashboard, project CRUD, profile & settings |
| `app/Http/Controllers/ProjectController.php` | Public project list/detail (published only) |
| `resources/views/admin/*`, `layouts/admin.blade.php`, `layouts/guest.blade.php` | Dashboard UI |
| `resources/css/admin.css`, `resources/js/admin.js` | Dashboard styles/behaviour (built alongside `app.css`) |
| `database/migrations/2026_10_21_*` | Additive migrations: `projects`, `settings`, `users.is_admin` |
| `app/Console/Commands/*` | `portfolio:make-admin`, `portfolio:import-crm-data` |

Full details, route table and known limitations: **`DELIVERABLES.md`**.
