<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['account_id', 'from_status', 'to_status', 'caller', 'reason', 'occurred_at'])]
final class AccountLifecycleEvent extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return ['occurred_at' => 'immutable_datetime'];
    }
}
