<?php

namespace App\Http\Controllers;

use App\Accounts\AccountResolver;
use App\Accounts\IdentityLinkRejected;
use App\Http\Requests\IdentityUnlinkRequest;
use Illuminate\Http\JsonResponse;

final class IdentityUnlinkController extends Controller
{
    public function __invoke(IdentityUnlinkRequest $request, string $account, AccountResolver $resolver): JsonResponse
    {
        if ($request->header('X-Zahir-Current-Account') !== $account) {
            return response()->json(['message' => 'Current account verification failed.'], 403);
        }

        $recoveryReference = $request->validated('accepted_recovery_reference');
        if ($recoveryReference !== null && ! $request->attributes->get('zahir.can_manage_account_lifecycle', false)) {
            return response()->json(['message' => 'Lifecycle authority is required.'], 403);
        }

        try {
            $result = $resolver->unlink(
                $account,
                $request->validated('provider'),
                $request->validated('provider_subject'),
                $request->attributes->get('zahir.caller'),
                $recoveryReference,
            );
        } catch (IdentityLinkRejected) {
            return response()->json(['message' => 'Identity unlinking failed.'], 409);
        }

        return response()->json([
            'account_id' => $result->accountId,
            'outcome' => $result->outcome,
        ]);
    }
}
