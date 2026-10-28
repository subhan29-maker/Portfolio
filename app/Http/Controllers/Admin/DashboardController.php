<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * The dashboard overview: counters and recent activity.
     */
    public function __invoke(): View
    {
        $counts = [
            'total' => Project::query()->count(),
            'published' => Project::query()->published()->count(),
            'draft' => Project::query()->draft()->count(),
            'unpublished' => Project::query()->unpublished()->count(),
            'featured' => Project::query()->featured()->count(),
            'placeholders' => Project::query()->where('is_demo', true)->count(),
        ];

        return view('admin.dashboard', [
            'counts' => $counts,
            'recentlyAdded' => Project::query()->latest()->take(5)->get(),
            'recentlyUpdated' => Project::query()->orderByDesc('updated_at')->take(5)->get(),
        ]);
    }
}
