<?php

namespace App\Models;

use App\Accounts\AccountStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['status'])]
class Account extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

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

    protected static function booted(): void
    {
        static::creating(function (Account $account): void {
            $account->id ??= 'acc_'.strtolower((string) Str::ulid());
        });
    }

    protected function casts(): array
    {
        return ['status' => AccountStatus::class];
    }
}
