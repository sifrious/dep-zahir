<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'account_id', 'provider', 'provider_subject', 'verified_claims',
    'provenance', 'linked_at', 'last_authenticated_at',
])]
class ExternalIdentity extends Model
{
    use HasUlids;

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    protected function casts(): array
    {
        return [
            'verified_claims' => 'array',
            'provenance' => 'array',
            'linked_at' => 'immutable_datetime',
            'last_authenticated_at' => 'immutable_datetime',
        ];
    }
}
