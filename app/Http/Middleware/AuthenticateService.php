<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateService
{
    public function handle(Request $request, Closure $next): Response
    {
        $provided = $request->bearerToken();
        foreach (config('zahir.service_tokens', []) as $caller => $token) {
            if (is_string($provided) && is_string($token) && hash_equals($token, $provided)) {
                $request->attributes->set('zahir.caller', (string) $caller);

                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
