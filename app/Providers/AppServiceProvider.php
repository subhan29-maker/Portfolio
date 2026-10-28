<?php

namespace App\Providers;

use App\Portfolio\ProjectRepository;
use App\Support\PortfolioSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ProjectRepository::class);
        $this->app->singleton(PortfolioSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->shareProfileWithViews();
        Paginator::defaultView('pagination.default');
    }

    /**
     * The contact form has its own limiter so a burst of messages cannot use
     * up the general web allowance for the rest of the site.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('contact', function (Request $request) {
            return [
                Limit::perMinute(3)->by('contact:'.$request->ip()),
                Limit::perDay(25)->by('contact-day:'.$request->ip()),
            ];
        });
    }

    /**
     * Every view receives the pieces the shared layout needs, so headers,
     * navigation, heroes, footers and forms stay consistent.
     */
    private function shareProfileWithViews(): void
    {
        $navigation = (array) config('portfolio.navigation', []);
        $technologies = (array) config('portfolio.technologies', []);

        View::composer('*', fn ($view) => $view->with([
            'profile' => $this->profileForRequest(),
            'navigation' => $navigation,
            'technologies' => $technologies,
        ]));
    }

    /**
     * Resolve the profile once for the current request, so every view on the
     * page agrees while a later request — or a save made in the dashboard —
     * always sees fresh values.
     */
    private function profileForRequest(): array
    {
        $attributes = request()->attributes;

        if (! $attributes->has('portfolio.profile')) {
            $attributes->set('portfolio.profile', $this->portfolioProfile());
        }

        return $attributes->get('portfolio.profile');
    }

    /**
     * The author profile shown across the public pages.
     *
     * The dashboard owns the editable values (name, title, introduction,
     * contact details, social links and the profile picture); the values in
     * config/portfolio.php stay the default so the site renders identically
     * before anything has been saved there.
     *
     * A settings lookup must never be able to break a page render, so any
     * failure simply falls back to the configured profile.
     *
     * @return array<string, mixed>
     */
    private function portfolioProfile(): array
    {
        try {
            return app(PortfolioSettings::class)->profile();
        } catch (Throwable) {
            return (array) config('portfolio.profile');
        }
    }
}
