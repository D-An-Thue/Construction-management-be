<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $claims = $request->attributes->get('jwt.claims', []);

        if (! is_array($claims)) {
            abort(403, 'Forbidden.');
        }

        $claimValues = $claims['permission'] ?? [];

        if (is_string($claimValues)) {
            $claimValues = [$claimValues];
        }

        if (! is_array($claimValues) || ! in_array($permission, $claimValues, true)) {
            abort(403, 'Forbidden.');
        }

        return $next($request);
    }
}
