<?php

namespace Tests\Feature;

use App\Portfolio\ProjectRepository;
use Database\Seeders\ProjectsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The public pages read published rows, so the starter content has to
        // be in the database exactly as it is on a freshly installed site.
        $this->seed(ProjectsSeeder::class);
    }

    public function test_home_page_renders_the_hero_and_featured_projects(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText('Subhan');
        $response->assertSeeText('Full-Stack Developer');
        $response->assertSeeText('View projects');
        $response->assertSeeText('Inventory Dashboard');
    }

    public function test_about_page_renders_experience_honestly(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertSeeText('Two years of learning, six months of building');
        $response->assertSeeText('Around six months of hands-on development');
    }

    public function test_skills_page_lists_skill_groups_and_technologies(): void
    {
        $response = $this->get(route('skills'));

        $response->assertOk();
        $response->assertSeeText('Frontend');
        $response->assertSeeText('Backend');
        $response->assertSeeText('Laravel');
        $response->assertSeeText('MySQL');
        $response->assertSeeText('Redis');
        $response->assertSeeText('Visual Studio Code');
    }

    public function test_projects_index_lists_every_project_and_flags_placeholders(): void
    {
        $response = $this->get(route('projects.index'));

        $response->assertOk();
        $response->assertSeeText('Inventory Dashboard');
        $response->assertSeeText('ThinkCode CRM');
        $response->assertSeeText('Portfolio CRM');
        $response->assertSeeText('Developer Portfolio');
        $response->assertSeeText('Developer Portfolio');

        // The "everything is a placeholder" notice is hidden once a real project is listed.
        $response->assertDontSeeText('These entries are placeholders');

        // The remaining demo entries are still labelled individually.
        $response->assertSeeText('Demo placeholder');
    }

    public function test_project_detail_page_renders_for_a_known_slug(): void
    {
        $response = $this->get(route('projects.show', 'inventory-dashboard'));

        $response->assertOk();
        $response->assertSeeText('Inventory Dashboard');
        $response->assertSeeText('Overview');
        $response->assertSeeText('Key features');
        $response->assertSeeText('Demo placeholder');
    }

    public function test_unknown_project_slug_returns_a_not_found_response(): void
    {
        $this->get(route('projects.show', 'this-project-does-not-exist'))->assertNotFound();
    }

    public function test_unknown_route_renders_the_custom_not_found_page(): void
    {
        $response = $this->get('/a-page-that-was-never-published');

        $response->assertNotFound();
        $response->assertSeeText("That page doesn't exist");
        $response->assertSeeText('Back to home');
    }

    public function test_every_page_has_a_unique_title_and_a_meta_description(): void
    {
        $pages = [
            route('home') => 'Subhan — Full-Stack Developer',
            route('about') => 'About — Subhan',
            route('skills') => 'Skills — Subhan',
            route('projects.index') => 'Projects — Subhan',
            route('contact') => 'Contact — Subhan',
            route('projects.show', 'inventory-dashboard') => 'Inventory Dashboard — Subhan',
        ];

        foreach ($pages as $url => $title) {
            $response = $this->get($url);

            $response->assertOk();
            $response->assertSee('<title>'.$title.'</title>', false);
            $response->assertSee('<meta name="description" content="', false);
            $response->assertSee('<link rel="canonical" href="'.$url.'">', false);
        }

        $this->assertSame(count($pages), count(array_unique($pages)), 'Page titles must be unique.');
    }

    public function test_navigation_links_to_every_main_page(): void
    {
        $response = $this->get(route('home'));

        foreach (['Home', 'About', 'Skills', 'Projects', 'Contact'] as $label) {
            $response->assertSeeText($label);
        }
    }

    public function test_project_repository_exposes_ordered_projects_and_related_ones(): void
    {
        $projects = app(ProjectRepository::class);

        $this->assertCount(4, $projects->all());
        $this->assertSame('inventory-dashboard', $projects->all()->first()->slug);

        $related = $projects->related($projects->findOrFail('inventory-dashboard'));

        $this->assertTrue($related->every(fn ($project) => $project->slug !== 'inventory-dashboard'));
    }
}
