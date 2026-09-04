<?php

namespace App\Http\Controllers;

use App\Accounts\ExternalIdentityLifecycle;
use App\Http\Requests\ExternalIdentityLifecycleRequest;
use Illuminate\Http\JsonResponse;
use Sifrious\Zahir\Authentication\V1\AuthenticationLifecycleSignal;
use Sifrious\Zahir\Authentication\V1\LoginOutcomeType;

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
            'identity_status' => $result->identityStatus->value,
            'result' => $result->result,
            'authentication_outcome' => LoginOutcomeType::ProviderFailed->value,
            'authentication_reason' => AuthenticationLifecycleSignal::ProviderRevoked->value,
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
            'identity_status' => $result->identityStatus->value,
            'result' => $result->result,
            'replayed' => $result->replayed,
        ]);
    }
}
