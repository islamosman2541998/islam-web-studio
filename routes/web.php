<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SiteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('home', ['locale' => session('studio_locale', 'ar')]));
Route::get('/admin/language/{locale}', LocaleController::class)->name('admin.locale')->whereIn('locale', ['ar', 'en']);
Route::get('/exports/{export}/download', ExportController::class)->middleware('auth')->name('exports.download');
Route::get('/private-assets/{asset}', AssetController::class)->middleware('auth')->name('assets.private');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SiteController::class, 'robots'])->name('robots');
Route::get('/{locale}/admin/{path?}', function (Request $request, string $locale, ?string $path = null) {
    $request->session()->put('studio_locale', $locale);

    return redirect('/admin'.($path ? '/'.$path : ''));
})->whereIn('locale', ['ar', 'en'])->where('path', '.*');
Route::prefix('{locale}')->whereIn('locale', ['ar', 'en'])->group(function () {
    Route::get('/', [SiteController::class, 'home'])->name('home');
    Route::get('/services', [SiteController::class, 'services'])->name('services.index');
    Route::get('/services/category/{slug}', [SiteController::class, 'serviceCategory'])->name('services.category');
    Route::get('/services/{slug}', [SiteController::class, 'service'])->name('services.show');
    Route::get('/work', [SiteController::class, 'projects'])->name('projects.index');
    Route::get('/work/category/{slug}', [SiteController::class, 'projectCategory'])->name('projects.category');
    Route::get('/work/{slug}', [SiteController::class, 'project'])->name('projects.show');
    Route::get('/journal', [SiteController::class, 'posts'])->name('posts.index');
    Route::get('/journal/category/{slug}', [SiteController::class, 'postCategory'])->name('posts.category');
    Route::get('/journal/{slug}', [SiteController::class, 'post'])->name('posts.show');
    Route::get('/about', [SiteController::class, 'about'])->name('about');
    Route::get('/process', [SiteController::class, 'methodology'])->name('methodology');
    Route::get('/testimonials', [SiteController::class, 'testimonials'])->name('testimonials');
    Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
    Route::get('/{slug}', [SiteController::class, 'page'])->name('pages.show');
});
