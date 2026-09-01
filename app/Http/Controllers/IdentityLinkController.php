<?php

namespace App\Http\Controllers;

use App\Accounts\AccountResolver;
use App\Accounts\IdentityCollision;
use App\Accounts\IdentityLinkRejected;
use App\Http\Requests\VerifiedExternalRequest;
use Illuminate\Http\JsonResponse;

final class IdentityLinkController extends Controller
{
    public function __invoke(VerifiedExternalRequest $request, string $account, AccountResolver $resolver): JsonResponse
    {
        if ($request->header('X-Zahir-Current-Account') !== $account) {
            return response()->json(['message' => 'Current account verification failed.'], 403);
        }

        try {
            $resolution = $resolver->link(
                $account,
                $request->verifiedExternal(),
                $request->attributes->get('zahir.caller'),
            );
        } catch (IdentityCollision|IdentityLinkRejected) {
            return response()->json(['message' => 'Identity linking failed.'], 409);
        }

        return response()->json([
            'account' => [
                'id' => $resolution->accountId,
                'status' => $resolution->status,
                'contact_email' => $resolution->contactEmail,
            ],
            'outcome' => 'linked',
        ]);
    }
}
