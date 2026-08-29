<?php

namespace App\Http\Controllers;

use App\Accounts\AccountLifecycle;
use App\Accounts\AccountStatus;
use App\Http\Requests\AccountLifecycleRequest;
use Illuminate\Http\JsonResponse;

final class AccountLifecycleController extends Controller
{
    public function suspend(AccountLifecycleRequest $request, string $account, AccountLifecycle $lifecycle): JsonResponse
    {
        return $this->change($request, $account, AccountStatus::Suspended, $lifecycle);
    }

    public function reactivate(AccountLifecycleRequest $request, string $account, AccountLifecycle $lifecycle): JsonResponse
    {
        return $this->change($request, $account, AccountStatus::Active, $lifecycle);
    }

    private function change(
        AccountLifecycleRequest $request,
        string $accountId,
        AccountStatus $status,
        AccountLifecycle $lifecycle,
    ): JsonResponse {
        $account = $lifecycle->change(
            $accountId,
            $status,
            (string) $request->attributes->get('zahir.caller'),
            $request->validated('reason'),
        );

        return response()->json(['account' => [
            'id' => $account->id,
            'status' => $account->status->value,
        ]]);
    }
}
