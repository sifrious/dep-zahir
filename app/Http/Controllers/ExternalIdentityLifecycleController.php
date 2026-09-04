<?php

namespace App\Http\Controllers;

use App\Accounts\ExternalIdentityLifecycle;
use App\Http\Requests\ExternalIdentityLifecycleRequest;
use Illuminate\Http\JsonResponse;

final class ExternalIdentityLifecycleController extends Controller
{
    public function revoke(
        ExternalIdentityLifecycleRequest $request,
        string $account,
        ExternalIdentityLifecycle $lifecycle,
    ): JsonResponse {
        $input = $request->validated();
        $result = $lifecycle->revoke(
            $account,
            $input['provider'],
            $input['provider_subject'],
            (string) $request->attributes->get('zahir.caller'),
            $input['reason_code'],
        );

        return response()->json([
            'account_id' => $result->accountId,
            'authentication_state' => $result->authenticationState->value,
            'replayed' => $result->replayed,
        ]);
    }

    public function recover(
        ExternalIdentityLifecycleRequest $request,
        string $account,
        ExternalIdentityLifecycle $lifecycle,
    ): JsonResponse {
        $input = $request->validated();
        $result = $lifecycle->recover(
            $account,
            $input['provider'],
            $input['provider_subject'],
            (string) $request->attributes->get('zahir.caller'),
            $input['reason_code'],
            $input['accepted_recovery_reference'] ?? '',
        );

        return response()->json([
            'account_id' => $result->accountId,
            'authentication_state' => $result->authenticationState->value,
            'replayed' => $result->replayed,
        ]);
    }
}
