<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'type', 'livemode', 'occurred_at', 'processed_at', 'payload_sha256'])]
class StripeEvent extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'livemode' => 'boolean',
            'occurred_at' => 'immutable_datetime',
            'processed_at' => 'immutable_datetime',
        ];
    }
}
