<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['account_id', 'customer_id'])]
class StripeCustomer extends Model
{
    use HasUlids;

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
