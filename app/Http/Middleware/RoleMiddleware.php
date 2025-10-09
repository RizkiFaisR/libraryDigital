<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // If no authenticated user, deny access (session-based auth expected)
        if (! $user) {
            // return 401 Unauthorized
            return response('', HttpResponse::HTTP_UNAUTHORIZED);
        }

        // If no role constraints provided, allow access
        if (empty($roles)) {
            return $next($request);
        }

        // allow when user's role is in the provided roles
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // forbidden otherwise
        return response('', HttpResponse::HTTP_FORBIDDEN);
    }
}
