<?php

namespace App\Http\Controllers;

use App\Accounts\EntitlementDecider;
use App\Http\Requests\EntitlementDecisionRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;

final class EntitlementDecisionController extends Controller
{
    public function __invoke(EntitlementDecisionRequest $request, EntitlementDecider $decider): JsonResponse
    {
        $input = $request->validated();
        $account = Account::query()->findOrFail($input['account_id']);
        $decision = $decider->decide($account, $input['product'], $input['entitlement']);

        return response()->json([
            'allowed' => $decision->allowed,
            'account_id' => $decision->accountId,
            'account_status' => $account->status->value,
            'product' => $decision->product,
            'entitlement' => $decision->entitlement,
            'evaluated_at' => $decision->evaluatedAt->toIso8601String(),
            'grant_id' => $decision->grantId,
        ]);
    }
}
