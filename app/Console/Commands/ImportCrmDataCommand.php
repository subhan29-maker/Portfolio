<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\ProjectsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PDO;
use Throwable;

/**
 * Bring the records of the old, separate Portfolio CRM into this application.
 *
 * The command is deliberately conservative: nothing is ever deleted, every
 * record is matched by its public slug, and an existing project is only
 * rewritten when it still carries the untouched starter content it was seeded
 * from (or when --update is passed explicitly). The old database is opened
 * read-only, so the original stays exactly as it was.
 *
 * Run it with --dry-run first to see the plan.
 */
class ImportCrmDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portfolio:import-crm-data
        {source? : Path to the old Portfolio CRM folder, or to its database/database.sqlite}
        {--update : Rewrite existing projects from the source, even ones that were edited here}
        {--dry-run : Show what would happen without changing anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import projects, images and administrator accounts from the old Portfolio CRM';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $source = $this->resolveSource();

        if ($source === null) {
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $pdo = $this->connectReadOnly($source);

        if ($pdo === null) {
            return self::FAILURE;
        }

        $this->info('Reading: '.$source);
        $this->newLine();

        $rows = $this->sourceProjects($pdo);
        [$created, $updated, $skipped] = $this->importProjects($rows, $dryRun);
        $images = $this->importImages($rows, $dryRun);

        // Anything the old application never had (the template's own
        // placeholders) is added afterwards, so the import alone produces the
        // complete set of content.
        $starter = $this->seedStarterContent($dryRun);
        $admins = $this->importAdministrators($pdo, $dryRun);
        $positions = $this->assignDisplayPositions($dryRun);

        $this->newLine();
        $this->table(
            ['Imported', 'Count'],
            [
                ['Projects created', $created],
                ['Projects updated', $updated],
                ['Projects left untouched', $skipped],
                ['Starter projects added', $starter],
                ['Images copied', $images],
                ['Administrator accounts', $admins],
                ['Display positions set', $positions],
            ],
        );

        if ($dryRun) {
            $this->comment('Dry run: nothing was changed. Run the same command without --dry-run to apply.');
        } else {
            $this->info('Done. Public pages only ever show published projects.');
        }

        return self::SUCCESS;
    }

    /*
    |------------------------------------------------------------------
    | Source
    |------------------------------------------------------------------
    */

    /**
     * Work out which database to read, or explain how to point at one.
     */
    private function resolveSource(): ?string
    {
        $given = trim((string) $this->argument('source'));

        $candidates = $given !== ''
            ? [$given]
            : [
                base_path('../portfolio-crm/database/database.sqlite'),
                base_path('portfolio-crm/database/database.sqlite'),
            ];

        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                $candidate = rtrim($candidate, '\\/').DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'database.sqlite';
            }

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $this->error('Could not find the old CRM database.');
        $this->line('Pass the path to the extracted CRM folder, or directly to its database file:');
        $this->line('  php artisan portfolio:import-crm-data "D:\path\to\portfolio-crm"');
        $this->line('The original application can be extracted from portfolio-crm.rar with 7-Zip or WinRAR.');

        return null;
    }

    /**
     * Open the old database read-only, so the original is never modified.
     */
    private function connectReadOnly(string $path): ?PDO
    {
        try {
            $pdo = new PDO('sqlite:'.$path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Belt and braces: even a stray UPDATE will be refused.
            $pdo->exec('PRAGMA query_only = ON');

            return $pdo;
        } catch (Throwable $exception) {
            $this->error('Could not open the old database: '.$exception->getMessage());

            return null;
        }
    }

    /**
     * Read every project row out of the old database.
     *
     * @return list<array<string, mixed>>
     */
    private function sourceProjects(PDO $pdo): array
    {
        $columns = $this->tableColumns($pdo, 'projects');

        if ($columns === []) {
            $this->warn('The source has no projects table, so no projects were imported.');

            return [];
        }

        // Very old copies of the CRM predate the placeholder flag.
        $hasIsDemo = in_array('is_demo', $columns, true);
        $rows = $pdo->query('SELECT * FROM projects ORDER BY id ASC')->fetchAll();

        foreach ($rows as $index => $row) {
            $rows[$index]['_is_demo_supported'] = $hasIsDemo;
        }

        return $rows;
    }

    /*
    |------------------------------------------------------------------
    | Projects
    |------------------------------------------------------------------
    */

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{0: int, 1: int, 2: int}
     */
    private function importProjects(array $rows, bool $dryRun): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $slug = (string) ($row['slug'] ?? '');

            if ($slug === '') {
                $skipped++;

                continue;
            }

            $attributes = $this->projectAttributes($row);
            $existing = Project::query()->where('slug', $slug)->first();

            if ($existing instanceof Project) {
                if ($this->needsNoChange($existing, $attributes)) {
                    $skipped++;

                    continue;
                }

                // A project somebody has already edited here is only touched
                // when they ask for it explicitly.
                $editable = (bool) $this->option('update')
                    || $this->stillMatchesStarterContent($existing, $slug);

                if (! $editable) {
                    $this->line("  <fg=yellow>skip</>  {$slug} — edited here; pass --update to overwrite");
                    $skipped++;

                    continue;
                }

                if (! $dryRun) {
                    $existing->fill($attributes);
                    $existing->save();
                }

                $this->line("  <fg=cyan>update</> {$slug}");
                $updated++;

                continue;
            }

            if (! $dryRun) {
                $project = new Project($attributes);

                // Keep the original id when it is still free, so anything
                // referring to it stays stable.
                $sourceId = (int) ($row['id'] ?? 0);

                if ($sourceId > 0 && ! Project::query()->whereKey($sourceId)->exists()) {
                    $project->id = $sourceId;
                }

                $project->save();
            }

            $this->line("  <fg=green>create</> {$slug}");
            $created++;
        }

        return [$created, $updated, $skipped];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function projectAttributes(array $row): array
    {
        $status = (string) ($row['status'] ?? 'draft');
        $hasIsDemo = (bool) ($row['_is_demo_supported'] ?? false);

        return [
            'title' => (string) ($row['title'] ?? ''),
            'summary' => $this->nullable($row['summary'] ?? null),
            'description' => $this->nullable($row['description'] ?? null),
            'problem' => $this->nullable($row['problem'] ?? null),
            'purpose' => $this->nullable($row['purpose'] ?? null),
            'features' => $this->decodeList($row['features'] ?? null),
            'highlights' => $this->decodeList($row['highlights'] ?? null),
            'contribution' => $this->nullable($row['contribution'] ?? null),
            'role' => $this->nullable($row['role'] ?? null),
            'year' => $this->nullable($row['year'] ?? null),
            'technologies' => $this->decodeList($row['technologies'] ?? null),
            'category' => $this->nullable($row['category'] ?? null),
            'live_demo_url' => $this->nullable($row['live_demo_url'] ?? null),
            'repository_url' => $this->nullable($row['repository_url'] ?? null),
            'is_featured' => (bool) ($row['is_featured'] ?? false),
            'is_demo' => $hasIsDemo ? (bool) ($row['is_demo'] ?? false) : false,
            'status' => in_array($status, ['draft', 'published', 'unpublished'], true) ? $status : 'draft',
            'cover_image' => $this->nullable($row['cover_image'] ?? null),
            'gallery' => $this->decodeList($row['gallery'] ?? null),
            'published_at' => $this->nullable($row['published_at'] ?? null),
        ];
    }

    /**
     * True when re-running the import would not change anything, which keeps
     * the command safe to run repeatedly.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function needsNoChange(Project $project, array $attributes): bool
    {
        foreach (['title', 'summary', 'description', 'problem', 'purpose', 'contribution', 'role', 'year', 'category', 'live_demo_url', 'repository_url', 'cover_image'] as $key) {
            if ((string) ($project->getAttribute($key) ?? '') !== (string) ($attributes[$key] ?? '')) {
                return false;
            }
        }

        foreach (['features', 'highlights', 'technologies', 'gallery'] as $key) {
            if (array_values((array) $project->getAttribute($key)) !== array_values((array) $attributes[$key])) {
                return false;
            }
        }

        return (bool) $project->is_featured === (bool) $attributes['is_featured']
            && (bool) $project->is_demo === (bool) $attributes['is_demo']
            && $project->status->value === $attributes['status'];
    }

    /**
     * True when a stored project still carries the exact starter content it
     * was seeded from, which means nobody has edited it yet.
     */
    private function stillMatchesStarterContent(Project $project, string $slug): bool
    {
        $starter = collect(config('portfolio.projects', []))->firstWhere('slug', $slug);

        if (! is_array($starter)) {
            return false;
        }

        return $project->title === ($starter['title'] ?? null)
            && $project->summary === ($starter['summary'] ?? null)
            && $project->description === ($starter['overview'] ?? null)
            && (bool) $project->is_demo === (bool) ($starter['is_demo'] ?? false);
    }

    /**
     * Add the starter projects that are still missing, so a single import run
     * leaves the site with the full set of content. It only ever creates.
     */
    private function seedStarterContent(bool $dryRun): int
    {
        $before = Project::query()->count();

        if ($dryRun) {
            $known = collect(config('portfolio.projects', []))->pluck('slug');
            $missing = Project::query()->whereIn('slug', $known)->count();

            return max(0, $known->count() - $missing);
        }

        (new ProjectsSeeder)->run();

        return Project::query()->count() - $before;
    }

    /*
    |------------------------------------------------------------------
    | Images
    |------------------------------------------------------------------
    */

    /**
     * Copy the cover and gallery files of every imported row into this
     * application's public storage, keeping their relative paths.
     *
     * @param  list<array<string, mixed>>  $rows
     */
    private function importImages(array $rows, bool $dryRun): int
    {
        $paths = [];

        foreach ($rows as $row) {
            if (filled($row['cover_image'] ?? null)) {
                $paths[] = (string) $row['cover_image'];
            }

            foreach ($this->decodeList($row['gallery'] ?? null) as $shot) {
                $paths[] = (string) $shot;
            }
        }

        $disk = Storage::disk('public');
        $copied = 0;

        foreach (array_unique($paths) as $relative) {
            $relative = ltrim($relative, '/');

            // Absolute URLs are left exactly as they are.
            if ($relative === '' || preg_match('#^(https?:)?//#i', $relative) === 1) {
                continue;
            }

            if ($disk->exists($relative)) {
                continue;
            }

            $source = $this->crmStoragePath($relative);

            if ($source === null) {
                $this->warn('  image not found in the old CRM: '.$relative);

                continue;
            }

            if (! $dryRun) {
                $disk->put($relative, (string) file_get_contents($source));
            }

            $copied++;
        }

        return $copied;
    }

    /**
     * Locate a storage file inside the old CRM folder, if it is still there.
     */
    private function crmStoragePath(string $relative): ?string
    {
        $given = trim((string) $this->argument('source'));

        $folders = $given !== ''
            ? [$given]
            : [base_path('../portfolio-crm'), base_path('portfolio-crm')];

        foreach ($folders as $folder) {
            if (is_file($folder)) {
                continue;
            }

            if (! is_dir($folder)) {
                continue;
            }

            $candidate = rtrim($folder, '\\/').'/storage/app/public/'.$relative;

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /*
    |------------------------------------------------------------------
    | Accounts and ordering
    |------------------------------------------------------------------
    */

    /**
     * Bring over the administrator accounts, keeping their password hashes so
     * the same sign-in details keep working.
     */
    private function importAdministrators(PDO $pdo, bool $dryRun): int
    {
        $columns = $this->tableColumns($pdo, 'users');

        if ($columns === []) {
            return 0;
        }

        $hasIsAdmin = in_array('is_admin', $columns, true);
        $rows = $pdo->query('SELECT * FROM users ORDER BY id ASC')->fetchAll();

        $imported = 0;

        foreach ($rows as $row) {
            // The old application only ever had administrator accounts; when
            // the flag column is missing, every row is treated as one.
            if ($hasIsAdmin && ! (bool) ($row['is_admin'] ?? false)) {
                continue;
            }

            $email = strtolower(trim((string) ($row['email'] ?? '')));

            if ($email === '' || blank($row['password'] ?? null)) {
                continue;
            }

            if (User::query()->where('email', $email)->exists()) {
                $this->line("  <fg=yellow>skip</>  account {$email} already exists");

                continue;
            }

            $this->line("  <fg=green>create</> administrator {$email}");

            if (! $dryRun) {
                User::query()->create([
                    'name' => (string) ($row['name'] ?? $email),
                    'email' => $email,
                    // Imported verbatim, so the same password keeps working.
                    'password' => (string) $row['password'],
                    'is_admin' => true,
                    'email_verified_at' => $row['email_verified_at'] ?? now(),
                ]);
            }

            $imported++;
        }

        return $imported;
    }

    /**
     * Give every project that has never been positioned one, following the
     * order the starter content was written in and appending anything else
     * after it. Positions an administrator has already set are left alone.
     */
    private function assignDisplayPositions(bool $dryRun): int
    {
        $pending = Project::query()
            ->where('sort_order', 0)
            ->orderBy('id')
            ->get();

        if ($pending->isEmpty()) {
            return 0;
        }

        $known = collect(config('portfolio.projects', []))->pluck('slug')->all();
        $next = max((int) Project::query()->max('sort_order'), count($known) * 10);
        $assigned = 0;

        foreach ($pending as $project) {
            $position = array_search($project->slug, $known, true);

            $project->sort_order = $position === false
                ? ($next += 10)
                : ($position + 1) * 10;

            if (! $dryRun) {
                $project->save();
            }

            $assigned++;
        }

        return $assigned;
    }

    /*
    |------------------------------------------------------------------
    | Helpers
    |------------------------------------------------------------------
    */

    /**
     * @return list<string>
     */
    private function tableColumns(PDO $pdo, string $table): array
    {
        try {
            return array_map(
                fn (array $column) => (string) $column['name'],
                $pdo->query("PRAGMA table_info('{$table}')")->fetchAll(),
            );
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Decode a JSON column written by the old application.
     *
     * @return list<string>
     */
    private function decodeList(mixed $value): array
    {
        if (blank($value)) {
            return [];
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($decoded)) {
            return [];
        }

        $items = [];

        foreach ($decoded as $item) {
            if (is_array($item)) {
                $item = $item['src'] ?? '';
            }

            if (is_string($item) && trim($item) !== '') {
                $items[] = trim($item);
            }
        }

        return $items;
    }

    private function nullable(mixed $value): ?string
    {
        return filled($value) ? (string) $value : null;
    }
}
