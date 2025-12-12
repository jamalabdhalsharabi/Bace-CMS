<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                abort(403, 'Unauthorized. Missing permission: ' . $permission);
            }
        }

        return $next($request);
    }
}
