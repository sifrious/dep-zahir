<?php

namespace App\Models;

use App\Accounts\ExternalIdentityStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'account_id', 'provider', 'provider_subject', 'status', 'verified_claims',
    'provenance', 'linked_at', 'last_authenticated_at', 'revoked_at', 'recovered_at',
])]
class ExternalIdentity extends Model
{
    use HasUlids;

    protected $attributes = [
        'status' => 'active',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ExternalIdentityStatus::class,
            'verified_claims' => 'array',
            'provenance' => 'array',
            'linked_at' => 'immutable_datetime',
            'last_authenticated_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'recovered_at' => 'immutable_datetime',
        ];
    }
}
