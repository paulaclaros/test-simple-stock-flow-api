<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use Closure;
use Illuminate\Http\Request;

final class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->attributes->get('auth_user');
        if (!$user) {
            return response('', 401);
        }

        $userRole = $user['role'] ?? null;
        if (!in_array($userRole, $roles, true)) {
            // D-C8: El 403 se mantiene sin cuerpo (Content-Length: 0)
            return response('', 403);
        }

        return $next($request);
    }
}
