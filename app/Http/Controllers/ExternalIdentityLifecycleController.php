<?php

namespace App\Http\Controllers;

use App\Accounts\ExternalIdentityLifecycle;
use App\Http\Requests\ExternalIdentityLifecycleRequest;
use Illuminate\Http\JsonResponse;
use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycleState;

final class ExternalIdentityLifecycleController extends Controller
{
    public function revoke(
        ExternalIdentityLifecycleRequest $request,
        string $account,
        ExternalIdentityLifecycle $lifecycle,
    ): JsonResponse {
        $input = $request->validated();
        $identity = $lifecycle->revoke(
            $account,
            $input['provider'],
            $input['provider_subject'],
            (string) $request->attributes->get('zahir.caller'),
            $input['reason_code'],
        );

        return response()->json([
            'account_id' => $identity->account_id,
            'authentication_state' => AuthenticationLifecycleState::ProviderRevoked->value,
        ]);
    }

    public function recover(
        ExternalIdentityLifecycleRequest $request,
        string $account,
        ExternalIdentityLifecycle $lifecycle,
    ): JsonResponse {
        $input = $request->validated();
        $identity = $lifecycle->recover(
            $account,
            $input['provider'],
            $input['provider_subject'],
            (string) $request->attributes->get('zahir.caller'),
            $input['reason_code'],
            $input['accepted_recovery_reference'] ?? '',
        );

        return response()->json([
            'account_id' => $identity->account_id,
            'authentication_state' => AuthenticationLifecycleState::Recovered->value,
        ]);
    }
}
