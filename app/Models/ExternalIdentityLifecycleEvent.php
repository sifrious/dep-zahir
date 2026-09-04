<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'account_id', 'provider', 'provider_subject_hash', 'from_status', 'to_status',
    'caller', 'reason_code', 'recovery_reference_hash', 'occurred_at',
])]
final class ExternalIdentityLifecycleEvent extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return ['occurred_at' => 'immutable_datetime'];
    }
}
