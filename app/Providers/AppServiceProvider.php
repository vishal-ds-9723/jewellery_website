<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::define('manage-enquiries', fn (User $user): bool => (bool) $user->is_admin);

        RateLimiter::for('enquiries', fn (Request $request) => [
            Limit::perMinute(5)->by('enquiry-minute:'.$request->ip()),
            Limit::perHour(30)->by('enquiry-hour:'.$request->ip()),
        ]);
        RateLimiter::for('newsletter', fn (Request $request) => [
            Limit::perMinute(3)->by('newsletter-minute:'.$request->ip()),
            Limit::perHour(20)->by('newsletter-hour:'.$request->ip()),
        ]);
        RateLimiter::for('exports', fn (Request $request) => Limit::perMinute(2)->by('export:'.$request->user()->id));
        RateLimiter::for('admin-login', fn (Request $request) => [
            Limit::perMinute(5)->by('login-ip:'.$request->ip()),
            Limit::perHour(20)->by('login-account:'.hash('sha256', mb_strtolower(is_string($request->input('email')) ? $request->input('email') : ''))),
        ]);
    }
}
