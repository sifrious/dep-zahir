<?php

namespace App\Http\Middleware;

use App\Models\ServiceRequestEvent;
use App\Services\ServiceCredentials;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateService
{
    public function __construct(private readonly ServiceCredentials $credentials) {}

    public function handle(Request $request, Closure $next): Response
    {
        $credential = $this->credentials->authenticate($request->bearerToken());
        if ($credential === null) {
            Log::warning('zahir.service_authentication', [
                'outcome' => 'denied',
                'route' => $request->path(),
                'metric_count' => 1,
            ]);

            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $caller = $credential->caller;
        $requestId = $request->headers->get('X-Request-ID') ?: (string) Str::uuid();
        $request->attributes->set('zahir.caller', $caller->key);
        $request->attributes->set('zahir.credential_id', $credential->id);
        $request->attributes->set('zahir.can_manage_account_lifecycle', $caller->can_manage_account_lifecycle);
        $startedAt = hrtime(true);
        $response = $next($request);

        ServiceRequestEvent::query()->create([
            'service_caller_id' => $caller->id,
            'service_credential_id' => $credential->id,
            'caller_key' => $caller->key,
            'method' => $request->method(),
            'route' => $request->path(),
            'response_status' => $response->getStatusCode(),
            'request_id' => $requestId,
            'occurred_at' => now(),
        ]);
        $response->headers->set('X-Request-ID', $requestId);
        Log::info('zahir.service_request', [
            'caller' => $caller->key,
            'credential_id' => $credential->id,
            'route' => $request->path(),
            'status' => $response->getStatusCode(),
            'request_id' => $requestId,
            'latency_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 3),
            'metric_count' => 1,
        ]);

        return $response;
    }
}
