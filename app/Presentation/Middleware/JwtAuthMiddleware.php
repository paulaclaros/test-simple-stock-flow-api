<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Application\Services\JwtTokenServiceInterface;
use Closure;
use Illuminate\Http\Request;

final class JwtAuthMiddleware
{
    public function __construct(
        private readonly JwtTokenServiceInterface $jwtTokenService
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response('', 401)->header('WWW-Authenticate', 'Bearer');
        }

        $token = substr($authHeader, 7);
        $payload = $this->jwtTokenService->validateToken($token);

        if ($payload === null) {
            return response('', 401)
                ->header('WWW-Authenticate', 'Bearer error="invalid_token"');
        }

        $request->attributes->set('auth_user', $payload);

        return $next($request);
    }
}
