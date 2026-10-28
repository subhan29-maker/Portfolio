<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allow only authenticated administrators into the dashboard.
 *
 * Authentication itself (the "auth" middleware) runs before this, so by the
 * time this executes a user is always present — but a signed-in non-admin is
 * still turned away.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Only administrators can manage this area.');
        }

        return $next($request);
    }
}
