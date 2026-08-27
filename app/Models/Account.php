<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['status', 'display_name'])]
class Account extends Model
{
    use HasUlids;

    protected $attributes = [
        'status' => 'active',
    ];

    public function externalIdentities(): HasMany
    {
        return $this->hasMany(ExternalIdentity::class);
    }

    public function entitlementGrants(): HasMany
    {
        return $this->hasMany(EntitlementGrant::class);
    }

    public function stripeCustomer(): HasOne
    {
        return $this->hasOne(StripeCustomer::class);
    }
}
