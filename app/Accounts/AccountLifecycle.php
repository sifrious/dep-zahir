<?php

namespace App\Accounts;

use App\Models\Account;
use App\Models\AccountLifecycleEvent;
use Illuminate\Support\Facades\DB;

final readonly class AccountLifecycle
{
    public function change(string $accountId, AccountStatus $status, string $caller, string $reason): Account
    {
        return DB::transaction(function () use ($accountId, $status, $caller, $reason): Account {
            $account = Account::query()->lockForUpdate()->findOrFail($accountId);
            $from = $account->status;

            if ($from !== $status) {
                $account->update(['status' => $status]);
            }

            AccountLifecycleEvent::query()->create([
                'account_id' => $account->id,
                'from_status' => $from->value,
                'to_status' => $status->value,
                'caller' => $caller,
                'reason' => $reason,
                'occurred_at' => now(),
            ]);

            return $account->refresh();
        }, 3);
    }
}
