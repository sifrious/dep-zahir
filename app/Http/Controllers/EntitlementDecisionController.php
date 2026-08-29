<?php

namespace App\Http\Controllers;

use App\Accounts\EntitlementDecider;
use App\Http\Requests\EntitlementDecisionRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class EntitlementDecisionController extends Controller
{
    public function __invoke(EntitlementDecisionRequest $request, EntitlementDecider $decider): JsonResponse
    {
        $startedAt = hrtime(true);
        $input = $request->validated();
        $account = Account::query()->findOrFail($input['account_id']);
        $decision = $decider->decide($account, $input['product'], $input['entitlement']);
        Log::info('zahir.entitlement_decision', [
            'outcome' => $decision->allowed ? 'allowed' : 'denied',
            'caller' => $request->attributes->get('zahir.caller'),
            'account_id' => $decision->accountId,
            'account_status' => $account->status->value,
            'product' => $decision->product,
            'entitlement' => $decision->entitlement,
            'latency_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 3),
            'metric_count' => 1,
        ]);

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
