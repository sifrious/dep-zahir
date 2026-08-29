<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['service_caller_id', 'service_credential_id', 'caller_key', 'method', 'route', 'response_status', 'request_id', 'occurred_at'])]
class ServiceRequestEvent extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return ['occurred_at' => 'immutable_datetime'];
    }
}
