<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\NewsletterController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// These display-only pages need no session file or CSRF cookie.
$stateless = [
    StartSession::class,
    ShareErrorsFromSession::class,
    PreventRequestForgery::class,
];

Route::view('/', 'pages.home')->name('home');
Route::view('/about', 'pages.about')->name('about')->withoutMiddleware($stateless);
Route::view('/collections', 'pages.collections')->name('collections')->withoutMiddleware($stateless);
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/product', 'pages.product')->name('product')->withoutMiddleware($stateless);

// Keep existing bookmarks and product query strings working.
foreach (['index' => 'home', 'about' => 'about', 'collections' => 'collections', 'contact' => 'contact', 'product' => 'product'] as $legacy => $route) {
    Route::get('/'.$legacy.'.html', fn (Request $request) => redirect()->route($route, $request->query(), 301));
}

Route::post('/enquiries', [EnquiryController::class, 'store'])->middleware('throttle:enquiries')->name('enquiries.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:newsletter')->name('newsletter.store');

Route::middleware('guest')->group(function () {
    Route::view('/admin/login', 'admin.login')->name('login');
    Route::post('/admin/login', [AdminSessionController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
});
Route::post('/admin/logout', [AdminSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'can:manage-enquiries'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::patch('/enquiries/{enquiry}', [AdminController::class, 'update'])->name('enquiries.update');
    Route::get('/enquiries/export', [AdminController::class, 'export'])->middleware('throttle:exports')->name('enquiries.export');
});
