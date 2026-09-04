<?php

namespace App\Http\Controllers;

use App\Accounts\AccountResolver;
use App\Accounts\IdentityCollision;
use App\Http\Requests\VerifiedExternalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class AccountResolutionController extends Controller
{
    public function __invoke(VerifiedExternalRequest $request, AccountResolver $resolver): JsonResponse
    {
        $startedAt = hrtime(true);
        try {
            $resolution = $resolver->resolve(
                $request->verifiedExternal(),
                $request->attributes->get('zahir.caller'),
            );
        } catch (IdentityCollision) {
            Log::warning('zahir.account_resolution', [
                'outcome' => 'collision',
                'caller' => $request->attributes->get('zahir.caller'),
                'latency_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 3),
                'metric_count' => 1,
            ]);

            return response()->json(['message' => 'Identity resolution failed.'], 409);
        }

        Log::info('zahir.account_resolution', [
            'outcome' => $resolution->created ? 'created' : 'resolved',
            'caller' => $request->attributes->get('zahir.caller'),
            'account_id' => $resolution->accountId,
            'latency_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 3),
            'metric_count' => 1,
        ]);

        return response()->json(['account' => [
            'id' => $resolution->accountId,
            'status' => $resolution->status,
            'created' => $resolution->created,
            'authentication_outcome' => $resolution->authenticationOutcome->value,
            'authentication_reason' => $resolution->authenticationReason,
        ]]);
    }
}
