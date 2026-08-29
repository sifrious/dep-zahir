<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['account_id', 'provider', 'provider_subject_hash', 'outcome', 'caller', 'provenance', 'occurred_at'])]
class AccountResolutionEvent extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return ['provenance' => 'array', 'occurred_at' => 'immutable_datetime'];
    }
}
