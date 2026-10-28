<?php

namespace App\Http\Controllers;

use App\Portfolio\ProjectRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly ProjectRepository $projects) {}

    public function home(): View
    {
        return view('home', [
            'featuredProjects' => $this->projects->take(3),
            'skillGroups' => config('portfolio.skills', []),
            'technologies' => config('portfolio.technologies', []),
        ]);
    }

    public function about(): View
    {
        return view('about');
    }

    public function skills(): View
    {
        return view('skills', [
            'skillGroups' => config('portfolio.skills', []),
            'technologies' => config('portfolio.technologies', []),
        ]);
    }

    public function sitemap(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('about'), 'priority' => '0.8'],
            ['loc' => route('skills'), 'priority' => '0.8'],
            ['loc' => route('projects.index'), 'priority' => '0.9'],
            ['loc' => route('contact'), 'priority' => '0.7'],
        ]);

        $projects = $this->projects->all()->filter(fn ($p) => ! $p->isDraft ?? true);
        $projects->each(function ($project) use ($urls) {
            $urls->push([
                'loc' => route('projects.show', $project->slug),
                'lastmod' => $project->updated_at ? Carbon::parse($project->updated_at)->toAtomString() : null,
                'priority' => '0.8',
            ]);
        });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
