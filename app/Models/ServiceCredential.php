<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_caller_id', 'label', 'secret_hash', 'expires_at', 'revoked_at', 'last_used_at'])]
class ServiceCredential extends Model
{
    use HasUlids;

    protected $hidden = ['secret_hash'];

    public function caller(): BelongsTo
    {
        return $this->belongsTo(ServiceCaller::class, 'service_caller_id');
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'last_used_at' => 'immutable_datetime',
        ];
    }
}
