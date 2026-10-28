<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use App\Portfolio\ProjectRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /*
    |------------------------------------------------------------------
    | Dashboard
    |------------------------------------------------------------------
    */

    public function test_the_dashboard_summarises_projects(): void
    {
        Project::factory()->count(2)->published()->create();
        Project::factory()->count(2)->create();
        Project::factory()->create();
        Project::factory()->published()->featured()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total projects')
            ->assertSee('Published')
            ->assertSee('Drafts')
            ->assertSee('Unpublished');
    }

    public function test_the_dashboard_links_to_the_quick_actions(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.projects.create'), false)
            ->assertSee(route('admin.projects.index'), false)
            ->assertSee(route('admin.profile.edit'), false);
    }

    /*
    |------------------------------------------------------------------
    | Listing, search and filters
    |------------------------------------------------------------------
    */

    public function test_the_project_index_lists_and_filters_projects(): void
    {
        Project::factory()->published()->create(['title' => 'Visible Project']);
        Project::factory()->create(['title' => 'Hidden Draft']);

        $this->actingAs($this->admin())
            ->get(route('admin.projects.index'))
            ->assertOk()
            ->assertSee('Visible Project')
            ->assertSee('Hidden Draft');

        $this->actingAs($this->admin())
            ->get(route('admin.projects.index', ['status' => 'published']))
            ->assertOk()
            ->assertSee('Visible Project')
            ->assertDontSee('Hidden Draft');
    }

    public function test_projects_can_be_searched_by_title_summary_and_category(): void
    {
        Project::factory()->published()->create([
            'title' => 'Inventory Dashboard',
            'category' => 'Dashboard',
        ]);
        Project::factory()->published()->create([
            'title' => 'Attendance System',
            'summary' => 'Tracks student attendance.',
            'category' => 'Web App',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.projects.index', ['q' => 'attendance']))
            ->assertOk()
            ->assertSee('Attendance System')
            ->assertDontSee('Inventory Dashboard');

        // A category matches too, not just titles and summaries.
        $this->actingAs($this->admin())
            ->get(route('admin.projects.index', ['q' => 'Web App']))
            ->assertOk()
            ->assertSee('Attendance System')
            ->assertDontSee('Inventory Dashboard');
    }

    public function test_the_project_list_is_paginated(): void
    {
        Project::factory()->count(15)->published()->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.projects.index'))
            ->assertOk();

        $projects = $response->viewData('projects');

        $this->assertInstanceOf(LengthAwarePaginator::class, $projects);
        $this->assertSame(10, $projects->count());
        $this->assertSame(15, $projects->total());
        $this->assertSame(2, $projects->lastPage());

        // Page two carries the query string, so filters survive paging.
        $pageTwo = $this->actingAs($this->admin())
            ->get(route('admin.projects.index', ['page' => 2, 'status' => 'published']))
            ->assertOk();

        preg_match_all('#href="([^"]*admin/projects\?[^"]*)"#', $pageTwo->getContent(), $matches);

        $previous = collect($matches[1] ?? [])
            ->first(fn (string $url) => str_contains($url, 'page=1'));

        $this->assertNotNull($previous, 'The pagination should link back to page one.');

        parse_str((string) parse_url(html_entity_decode($previous), PHP_URL_QUERY), $query);

        $this->assertSame('published', $query['status'] ?? null);
        $this->assertSame('1', $query['page'] ?? null);
    }

    public function test_an_empty_result_explains_itself(): void
    {
        Project::factory()->published()->create(['title' => 'Only Project']);

        $this->actingAs($this->admin())
            ->get(route('admin.projects.index', ['q' => 'nothing-matches-this']))
            ->assertOk()
            ->assertSee('Nothing matches those filters')
            ->assertSee('Clear filters');
    }

    /*
    |------------------------------------------------------------------
    | Creating and editing
    |------------------------------------------------------------------
    */

    public function test_the_create_form_offers_every_field(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.projects.create'))
            ->assertOk()
            ->assertSee('Project details')
            ->assertSee('The story behind it')
            ->assertSee('Problem it solves')
            ->assertSee('Why it was built')
            ->assertSee('Your contribution')
            ->assertSee('Detail page content')
            ->assertSee('Features')
            ->assertSee('Highlights')
            ->assertSee('Technologies')
            ->assertSee('Category')
            ->assertSee('Your role')
            ->assertSee('Year')
            ->assertSee('Live demo URL')
            ->assertSee('Repository URL')
            ->assertSee('Publishing')
            ->assertSee('Display position')
            ->assertSee('Cover image')
            ->assertSee('Gallery')
            ->assertSee('Create project');
    }

    public function test_a_project_can_be_created_with_parsed_lists(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), [
                'title' => 'My New Project',
                'summary' => 'A short summary.',
                'description' => 'A much longer description.',
                'problem' => 'Something was missing.',
                'purpose' => 'This is what it fixes.',
                'features' => "First feature, with a comma.\nSecond feature.\n\n  ",
                'highlights' => "First highlight.\nSecond highlight.",
                'contribution' => 'What I built.',
                'role' => 'Design and development',
                'year' => '2026',
                'technologies' => 'PHP, Laravel,  Redis',
                'category' => 'Web App',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $project = Project::query()->firstWhere('slug', 'my-new-project');

        $this->assertNotNull($project);
        $this->assertSame(['PHP', 'Laravel', 'Redis'], $project->technologies);
        // Features are sentences, so only line breaks split them.
        $this->assertSame(['First feature, with a comma.', 'Second feature.'], $project->features);
        $this->assertSame(['First highlight.', 'Second highlight.'], $project->highlights);
        $this->assertSame(ProjectStatus::Draft, $project->status);
        $this->assertNull($project->published_at);
    }

    public function test_optional_detail_page_fields_stay_empty_when_left_blank(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), [
                'title' => 'Narrative Project',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $project = Project::query()->firstWhere('slug', 'narrative-project');

        $this->assertNotNull($project);
        $this->assertNull($project->problem);
        $this->assertNull($project->purpose);
        $this->assertSame([], (array) $project->features);
        $this->assertSame([], (array) $project->highlights);
        $this->assertNull($project->contribution);
        $this->assertNull($project->role);
        $this->assertNull($project->year);
        $this->assertNull($project->live_demo_url);
        $this->assertNull($project->repository_url);
    }

    public function test_a_project_can_be_updated_without_changing_its_slug(): void
    {
        $project = Project::factory()->create(['title' => 'Original Title']);

        $this->actingAs($this->admin())
            ->put(route('admin.projects.update', $project), [
                'title' => 'A Completely Different Title',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $project->refresh();

        $this->assertSame('A Completely Different Title', $project->title);
        $this->assertSame('original-title', $project->slug);
    }

    public function test_the_edit_form_is_prefilled(): void
    {
        $project = Project::factory()->published()->create([
            'title' => 'Prefilled Project',
            'summary' => 'Shown in the field.',
            'technologies' => ['Laravel', 'Redis'],
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.projects.edit', $project))
            ->assertOk()
            ->assertSee('Prefilled Project')
            ->assertSee('Shown in the field.')
            ->assertSee('Laravel, Redis')
            ->assertSee('Save changes');
    }

    /*
    |------------------------------------------------------------------
    | Validation
    |------------------------------------------------------------------
    */

    public function test_a_title_is_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), ['status' => 'draft'])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_a_status_is_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), ['title' => 'No Status'])
            ->assertSessionHasErrors('status');
    }

    public function test_an_unknown_status_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), [
                'title' => 'Odd Status',
                'status' => 'archived',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_generated_slugs_are_unique(): void
    {
        Project::factory()->create(['title' => 'Duplicate Name']);

        $this->actingAs($this->admin())->post(route('admin.projects.store'), [
            'title' => 'Duplicate Name',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('projects', ['slug' => 'duplicate-name-2']);
    }

    public function test_a_manually_supplied_slug_must_be_unique(): void
    {
        Project::factory()->create(['slug' => 'taken-slug']);

        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), [
                'title' => 'Another Project',
                'slug' => 'taken-slug',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_invalid_links_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), [
                'title' => 'Bad Links',
                'status' => 'draft',
                'live_demo_url' => 'not-a-url',
                'repository_url' => 'also-not-a-url',
            ])
            ->assertSessionHasErrors(['live_demo_url', 'repository_url']);
    }

    public function test_non_image_uploads_are_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), [
                'title' => 'Bad Upload',
                'status' => 'draft',
                'cover_image' => UploadedFile::fake()->create('notes.txt', 4, 'text/plain'),
            ])
            ->assertSessionHasErrors('cover_image');

        // Nothing was written and no half-finished record was left behind.
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_a_validation_failure_keeps_the_form_filled_in(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.projects.create'))
            ->post(route('admin.projects.store'), [
                'title' => '',
                'status' => 'draft',
                'summary' => 'Kept safe for the next attempt.',
            ])
            ->assertRedirect(route('admin.projects.create'))
            ->assertSessionHasErrors('title')
            ->assertSessionHasInput('summary', 'Kept safe for the next attempt.');
    }

    /*
    |------------------------------------------------------------------
    | Publishing and public visibility
    |------------------------------------------------------------------
    */

    public function test_a_project_can_be_published_and_unpublished(): void
    {
        $project = Project::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.projects.publish', $project))
            ->assertRedirect();

        $project->refresh();
        $this->assertTrue($project->isPublished());
        $this->assertNotNull($project->published_at);

        $this->actingAs($admin)
            ->patch(route('admin.projects.unpublish', $project))
            ->assertRedirect();

        $this->assertSame(ProjectStatus::Unpublished, $project->fresh()->status);
    }

    public function test_only_published_projects_reach_the_public_pages(): void
    {
        Project::factory()->published()->create(['title' => 'Live Project', 'summary' => 'Publicly visible.']);
        Project::factory()->create(['title' => 'Draft Project', 'summary' => 'Still being written.']);
        Project::factory()->unpublished()->create(['title' => 'Hidden Project', 'summary' => 'Taken down.']);

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Live Project')
            ->assertDontSee('Draft Project')
            ->assertDontSee('Hidden Project');

        $this->get(route('projects.show', 'live-project'))->assertOk()->assertSee('Live Project');
        $this->get(route('projects.show', 'draft-project'))->assertNotFound();
        $this->get(route('projects.show', 'hidden-project'))->assertNotFound();
    }

    public function test_an_admin_edit_never_changes_the_public_page_by_accident(): void
    {
        $project = Project::factory()->published()->create(['title' => 'Careful Project']);

        $this->actingAs($this->admin())
            ->put(route('admin.projects.update', $project), [
                'title' => 'Careful Project',
                'summary' => 'Now with a better summary.',
                'status' => 'published',
            ])
            ->assertRedirect();

        $this->get(route('projects.show', 'careful-project'))
            ->assertOk()
            ->assertSee('Now with a better summary.');
    }

    public function test_public_projects_are_shown_in_their_display_order(): void
    {
        Project::factory()->published()->create(['title' => 'Third', 'sort_order' => 30]);
        Project::factory()->published()->create(['title' => 'First', 'sort_order' => 10]);
        Project::factory()->published()->create(['title' => 'Second', 'sort_order' => 20]);

        $slugs = app(ProjectRepository::class)->all()->pluck('slug');

        $this->assertSame(['first', 'second', 'third'], $slugs->all());
    }

    /*
    |------------------------------------------------------------------
    | Images
    |------------------------------------------------------------------
    */

    public function test_a_cover_image_is_stored_and_removed_with_the_project(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), [
                'title' => 'Project With Cover',
                'status' => 'draft',
                'cover_image' => $this->fakeImage('cover.png'),
            ])
            ->assertRedirect();

        $project = Project::query()->firstWhere('title', 'Project With Cover');

        $this->assertNotNull($project->cover_image);
        $this->assertStringStartsWith('projects/covers/', $project->cover_image);
        Storage::disk('public')->assertExists($project->cover_image);

        $this->actingAs($this->admin())
            ->delete(route('admin.projects.destroy', $project))
            ->assertRedirect(route('admin.projects.index'));

        Storage::disk('public')->assertMissing($project->cover_image);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_a_cover_can_be_replaced_and_the_old_file_removed(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), [
                'title' => 'Replacing Cover',
                'status' => 'draft',
                'cover_image' => $this->fakeImage('first.png'),
            ]);

        $project = Project::query()->firstWhere('title', 'Replacing Cover');
        $first = $project->cover_image;

        $this->actingAs($this->admin())
            ->put(route('admin.projects.update', $project), [
                'title' => 'Replacing Cover',
                'status' => 'draft',
                'cover_image' => $this->fakeImage('second.png'),
            ]);

        $project->refresh();

        $this->assertNotSame($first, $project->cover_image);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($project->cover_image);
    }

    public function test_the_cover_can_be_removed_on_its_own(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('projects/covers/keep-me.png', 'x');

        $project = Project::factory()->create([
            'cover_image' => 'projects/covers/keep-me.png',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.projects.update', $project), [
                'title' => $project->title,
                'status' => 'draft',
                'remove_cover' => '1',
            ])
            ->assertRedirect();

        $this->assertNull($project->fresh()->cover_image);
        Storage::disk('public')->assertMissing('projects/covers/keep-me.png');
    }

    public function test_gallery_images_can_be_added_and_removed(): void
    {
        Storage::fake('public');

        $project = Project::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.projects.update', $project), [
                'title' => $project->title,
                'status' => 'draft',
                'gallery' => [
                    $this->fakeImage('one.png'),
                    $this->fakeImage('two.png'),
                ],
            ])
            ->assertRedirect();

        $project->refresh();
        $this->assertCount(2, $project->gallery);

        Storage::disk('public')->assertExists($project->gallery[0]);
        Storage::disk('public')->assertExists($project->gallery[1]);

        $this->actingAs($this->admin())
            ->delete(route('admin.projects.gallery.destroy', ['project' => $project, 'index' => 0]))
            ->assertRedirect();

        $this->assertCount(1, $project->fresh()->gallery);
        Storage::disk('public')->assertMissing($project->gallery[0]);
    }

    public function test_images_are_replaced_by_new_uploads_not_appended(): void
    {
        Storage::fake('public');

        $project = Project::factory()->create([
            'gallery' => ['projects/gallery/old.png'],
        ]);

        Storage::disk('public')->put('projects/gallery/old.png', 'x');

        $this->actingAs($this->admin())
            ->put(route('admin.projects.update', $project), [
                'title' => $project->title,
                'status' => 'draft',
                'gallery' => [$this->fakeImage('new.png')],
            ]);

        $gallery = $project->fresh()->gallery;

        $this->assertCount(2, $gallery, 'Unchecked gallery images are kept; new ones are added.');
        Storage::disk('public')->assertExists('projects/gallery/old.png');
    }

    /*
    |------------------------------------------------------------------
    | Authorisation
    |------------------------------------------------------------------
    */

    public function test_the_dashboard_is_not_reachable_by_visitors(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.projects.index'))->assertRedirect(route('login'));
        $this->get(route('admin.projects.create'))->assertRedirect(route('login'));
        $this->post(route('admin.projects.store'), [])->assertRedirect(route('login'));
        $this->delete(route('admin.projects.destroy', 'some-project'))->assertRedirect(route('login'));
    }

    public function test_the_dashboard_is_not_linked_from_the_public_site(): void
    {
        foreach (['/', '/about', '/skills', '/projects', '/contact'] as $page) {
            $this->get($page)
                ->assertOk()
                ->assertDontSee('/admin', false)
                ->assertDontSee('/login', false);
        }
    }
}
