<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseCachePolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // These pages contain no session data or form tokens.
        if ($request->isMethod('GET') && $request->routeIs('about', 'collections', 'product') && $response->isSuccessful()) {
            $response->headers->set('Cache-Control', 'public, max-age=60');
        } else {
            // Never share form tokens, submission responses, or customer/admin details.
            $response->headers->set('Cache-Control', 'private, no-store');
        }

        return $response;
    }
}
