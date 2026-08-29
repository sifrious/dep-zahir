<?php

namespace App\Http\Controllers;

use App\Accounts\AccountResolver;
use App\Accounts\IdentityCollision;
use App\Http\Requests\VerifiedExternalRequest;
use Illuminate\Http\JsonResponse;

final class AccountResolutionController extends Controller
{
    public function __invoke(VerifiedExternalRequest $request, AccountResolver $resolver): JsonResponse
    {
        try {
            $resolution = $resolver->resolve(
                $request->verifiedExternal(),
                $request->attributes->get('zahir.caller'),
            );
        } catch (IdentityCollision) {
            return response()->json(['message' => 'Identity resolution failed.'], 409);
        }

        return response()->json(['account' => [
            'id' => $resolution->accountId,
            'status' => $resolution->status,
            'created' => $resolution->created,
        ]]);
    }
}
