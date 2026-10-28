<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Portfolio\ProjectRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PDO;
use Tests\TestCase;

/**
 * The one-time migration from the old, separate Portfolio CRM.
 *
 * Each test builds a throwaway copy of the old application (its SQLite
 * database plus its stored images) in a temporary folder, so the command is
 * exercised exactly as it is used for real — and the original archive is
 * never touched.
 */
class ImportCrmDataCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $source;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->source = sys_get_temp_dir().DIRECTORY_SEPARATOR.'portfolio-crm-source-'.uniqid();

        mkdir($this->source.'/database', 0755, true);
        mkdir($this->source.'/storage/app/public/projects/covers', 0755, true);
        mkdir($this->source.'/storage/app/public/projects/gallery', 0755, true);

        $this->buildSourceDatabase();

        file_put_contents($this->source.'/storage/app/public/projects/covers/thinkcode.png', 'cover-bytes');
        file_put_contents($this->source.'/storage/app/public/projects/gallery/students.png', 'gallery-bytes');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->source);

        parent::tearDown();
    }

    /*
    |------------------------------------------------------------------
    | The fake old application
    |------------------------------------------------------------------
    */

    private function buildSourceDatabase(): void
    {
        $pdo = new PDO('sqlite:'.$this->source.'/database/database.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec('CREATE TABLE projects (
            id INTEGER PRIMARY KEY,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            summary TEXT,
            description TEXT,
            problem TEXT,
            purpose TEXT,
            features TEXT,
            highlights TEXT,
            contribution TEXT,
            role TEXT,
            year TEXT,
            technologies TEXT,
            category TEXT,
            live_demo_url TEXT,
            repository_url TEXT,
            is_featured INTEGER DEFAULT 0,
            status TEXT DEFAULT "draft",
            cover_image TEXT,
            gallery TEXT,
            published_at TEXT,
            created_at TEXT,
            updated_at TEXT
        )');

        $pdo->exec('CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            name TEXT,
            email TEXT UNIQUE,
            password TEXT,
            email_verified_at TEXT,
            remember_token TEXT,
            is_admin INTEGER DEFAULT 0,
            created_at TEXT,
            updated_at TEXT
        )');

        $insert = $pdo->prepare('INSERT INTO projects
            (id, title, slug, summary, description, problem, purpose, features, highlights,
             contribution, role, year, technologies, category, live_demo_url, repository_url,
             is_featured, status, cover_image, gallery, published_at, created_at, updated_at)
            VALUES
            (1, "ThinkCode CRM", "thinkcode-crm", "A CRM built for a coaching institute.",
             "Full description from the old system.", "Records lived in spreadsheets.",
             "Keep them in one database.", :features, :highlights,
             "Schema, controllers and views.", "Full-stack developer", "2026", :technologies,
             "Web App", "https://thinkcode.example.com", "https://github.com/subhan/thinkcode",
             1, "published", "projects/covers/thinkcode.png", :gallery,
             "2026-10-14 10:00:00", "2026-10-14 10:00:00", "2026-10-15 09:30:00")');

        $insert->execute([
            'features' => json_encode(['Student records.', 'Attendance tracking.']),
            'highlights' => json_encode(['Immutable movement entries.']),
            'technologies' => json_encode(['Laravel', 'MySQL', 'Livewire']),
            'gallery' => json_encode(['projects/gallery/students.png']),
        ]);

        $pdo->exec('INSERT INTO projects (id, title, slug, summary, status, is_featured, published_at)
            VALUES (2, "Portfolio CRM", "portfolio-crm", "A small admin for this portfolio.",
                    "published", 0, "2026-10-16 08:00:00")');

        $pdo->exec('INSERT INTO projects (id, title, slug, summary, status)
            VALUES (3, "Abandoned Idea", "abandoned-idea", "Never finished.", "draft")');

        $pdo->exec('INSERT INTO users (id, name, email, password, email_verified_at, is_admin)
            VALUES (1, "Subhan", "admin@portfolio-crm.test", "'.Hash::make('original-password').'", "2026-10-14 10:00:00", 1)');
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$entry;

            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }

        @rmdir($directory);
    }

    /**
     * @param  array<string, bool>  $options
     */
    private function import(array $options = []): int
    {
        return $this->artisan('portfolio:import-crm-data', array_merge(
            ['source' => $this->source],
            $options,
        ))->run();
    }

    /*
    |------------------------------------------------------------------
    | Tests
    |------------------------------------------------------------------
    */

    public function test_a_dry_run_reports_the_plan_and_changes_nothing(): void
    {
        $this->import(['--dry-run' => true]);

        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('users', 0);
        Storage::disk('public')->assertMissing('projects/covers/thinkcode.png');
    }

    public function test_projects_images_and_accounts_are_imported(): void
    {
        $this->import();

        // Real records, keeping their original ids and slugs.
        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'slug' => 'thinkcode-crm',
            'title' => 'ThinkCode CRM',
            'status' => 'published',
            'is_featured' => 1,
        ]);

        $thinkcode = Project::query()->where('slug', 'thinkcode-crm')->first();

        $this->assertSame(['Laravel', 'MySQL', 'Livewire'], $thinkcode->technologies);
        $this->assertSame(['Student records.', 'Attendance tracking.'], $thinkcode->features);
        $this->assertSame('Records lived in spreadsheets.', $thinkcode->problem);
        $this->assertSame('projects/covers/thinkcode.png', $thinkcode->cover_image);
        $this->assertSame(['projects/gallery/students.png'], $thinkcode->gallery);
        $this->assertNotNull($thinkcode->published_at);

        // The draft from the old system stays a draft.
        $abandoned = Project::query()->where('slug', 'abandoned-idea')->firstOrFail();

        $this->assertSame('draft', $abandoned->status->value);

        // Images came across with their original paths.
        Storage::disk('public')->assertExists('projects/covers/thinkcode.png');
        Storage::disk('public')->assertExists('projects/gallery/students.png');
        $this->assertSame('cover-bytes', Storage::disk('public')->get('projects/covers/thinkcode.png'));

        // The administrator keeps the same sign-in details.
        $admin = User::query()->where('email', 'admin@portfolio-crm.test')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('original-password', $admin->password));
    }

    public function test_the_starter_projects_fill_the_gaps(): void
    {
        $this->import();

        // The template's own placeholders are added, and the display order
        // matches the order the public site has always used.
        $this->assertDatabaseHas('projects', ['slug' => 'inventory-dashboard']);
        $this->assertDatabaseHas('projects', ['slug' => 'developer-portfolio']);

        $this->assertSame(
            ['inventory-dashboard', 'thinkcode-crm', 'portfolio-crm', 'developer-portfolio', 'abandoned-idea'],
            Project::query()->ordered()->pluck('slug')->all(),
        );
    }

    public function test_only_published_projects_reach_the_public_pages_after_import(): void
    {
        $this->import();

        $public = app(ProjectRepository::class)->all()->pluck('slug');

        $this->assertNotContains('abandoned-idea', $public);
        $this->assertContains('thinkcode-crm', $public);
    }

    public function test_running_the_import_twice_changes_nothing(): void
    {
        $this->import();

        $before = Project::query()->orderBy('id')->get()->toArray();

        $this->import();

        $this->assertSame($before, Project::query()->orderBy('id')->get()->toArray());
        $this->assertDatabaseCount('projects', 5);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_projects_edited_here_are_protected_from_overwrite(): void
    {
        $this->import();

        Project::query()->where('slug', 'thinkcode-crm')->update([
            'summary' => 'Rewritten in the dashboard.',
        ]);

        $this->import();

        $this->assertSame(
            'Rewritten in the dashboard.',
            Project::query()->where('slug', 'thinkcode-crm')->value('summary'),
        );
    }

    public function test_update_forces_the_source_content_back_in(): void
    {
        $this->import();

        Project::query()->where('slug', 'thinkcode-crm')->update([
            'summary' => 'Rewritten in the dashboard.',
        ]);

        $this->import(['--update' => true]);

        $this->assertSame(
            'A CRM built for a coaching institute.',
            Project::query()->where('slug', 'thinkcode-crm')->value('summary'),
        );
    }

    public function test_the_original_database_is_never_written_to(): void
    {
        $this->import();

        $pdo = new PDO('sqlite:'.$this->source.'/database/database.sqlite');
        $rows = $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();

        $this->assertSame('3', (string) $rows);
    }

    public function test_the_command_fails_helpfully_without_a_source(): void
    {
        $this->artisan('portfolio:import-crm-data', ['source' => 'D:/nowhere/missing-crm'])
            ->assertFailed();

        $this->assertDatabaseCount('projects', 0);
    }
}
