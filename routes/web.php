<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public portfolio
|--------------------------------------------------------------------------
|
| These URLs are the site visitors see and must not change: home, about,
| skills, the project list and detail pages, and the contact form.
|
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/skills', [PageController::class, 'skills'])->name('skills');
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');

Route::resource('projects', ProjectController::class)->only(['index', 'show']);

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');

/*
|--------------------------------------------------------------------------
| Administrator sign-in
|--------------------------------------------------------------------------
|
| There is no public registration: accounts only exist when they are created
| with "php artisan portfolio:make-admin". Guests who reach /admin/* are
| redirected here by the auth middleware, and signing in lands on the
| dashboard.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin dashboard
|--------------------------------------------------------------------------
|
| Everything behind "auth" (a signed-in user) and "admin" (an administrator).
| Nothing here is reachable by visitors, and none of it is linked from the
| public pages.
|
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [DashboardController::class, '__invoke'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/picture', [ProfileController::class, 'destroyPicture'])->name('profile.picture.destroy');

    Route::get('projects', [AdminProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/create', [AdminProjectController::class, 'create'])->name('projects.create');
    Route::post('projects', [AdminProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [AdminProjectController::class, 'show'])->name('projects.show');
    Route::get('projects/{project}/edit', [AdminProjectController::class, 'edit'])->name('projects.edit');
    Route::put('projects/{project}', [AdminProjectController::class, 'update'])->name('projects.update');

    Route::patch('projects/{project}/publish', [AdminProjectController::class, 'publish'])->name('projects.publish');
    Route::patch('projects/{project}/unpublish', [AdminProjectController::class, 'unpublish'])->name('projects.unpublish');

    Route::delete('projects/{project}/gallery/{index}', [AdminProjectController::class, 'destroyGalleryImage'])
        ->whereNumber('index')
        ->name('projects.gallery.destroy');

    Route::delete('projects/{project}', [AdminProjectController::class, 'destroy'])->name('projects.destroy');
});
