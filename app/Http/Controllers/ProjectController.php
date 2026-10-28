<?php

namespace App\Http\Controllers;

use App\Portfolio\Project;
use App\Portfolio\ProjectRepository;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectRepository $projects) {}

    public function index(): View
    {
        return view('projects.index', [
            'projects' => $this->projects->all(),
            'showingDemoNotice' => $this->projects->onlyContainsDemoProjects(),
        ]);
    }

    /**
     * The {project} route parameter is the public slug. Only published
     * projects are resolvable, so drafts and unpublished projects behave
     * exactly like unknown slugs and return a 404.
     */
    public function show(string $project): View
    {
        $resolved = $this->projects->findOrFail($project);

        return view('projects.show', [
            'project' => $resolved,
            'relatedProjects' => $this->projects->related($resolved),
        ]);
    }
}
