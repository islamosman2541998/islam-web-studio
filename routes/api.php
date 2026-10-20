<?php

use App\Http\Controllers\Api\V1\AppController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Middleware\SetApiLocale;
use Illuminate\Support\Facades\Route;

/*
| Public, read-only API for the mobile app (no login). Language: `?lang=ar|en` or the
| `Accept-Language` header. Records are addressed by numeric id or by slug in either language.
*/
Route::prefix('v1')->name('api.v1.')->middleware([SetApiLocale::class, 'throttle:api'])->group(function () {
    Route::middleware('cache.headers:public;max_age=60;etag')->group(function () {
        Route::get('config', [AppController::class, 'config'])->name('config');
        Route::get('translations', [AppController::class, 'translations'])->name('translations');
        Route::get('home', [AppController::class, 'home'])->name('home');
        Route::get('search', [AppController::class, 'search'])->name('search');

        Route::get('service-categories', [ServiceController::class, 'categories'])->name('services.categories');
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('services/{service}', [ServiceController::class, 'show'])->name('services.show');

        Route::get('project-categories', [ProjectController::class, 'categories'])->name('projects.categories');
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

        Route::get('post-categories', [PostController::class, 'categories'])->name('posts.categories');
        Route::get('tags', [PostController::class, 'tags'])->name('posts.tags');
        Route::get('posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');

        Route::get('pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('pages/{page}', [PageController::class, 'show'])->name('pages.show');
        Route::get('about', [PageController::class, 'about'])->name('about');

        Route::get('testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
        Route::get('methodology', [TestimonialController::class, 'methodology'])->name('methodology');
    });

    Route::post('leads', [LeadController::class, 'store'])->middleware('throttle:api-leads')->name('leads.store');
});
