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

    /**
     * The address to reach this account at.
     *
     * An account may hold several linked identities, each asserting its own
     * email, so "the account's address" needs a rule rather than a guess. The
     * most recently authenticated identity wins: it is the one the person
     * demonstrably still controls. Ties, and identities never yet used to sign
     * in, fall back to the most recently linked.
     *
     * Null is a real answer. An identity may assert no email at all, and
     * inventing one would be worse than saying so.
     */
    public function contactEmail(): ?string
    {
        $identity = $this->externalIdentities()
            ->orderByRaw('last_authenticated_at IS NULL')
            ->orderByDesc('last_authenticated_at')
            ->orderByDesc('linked_at')
            ->first();

        $email = $identity?->verified_claims['email'] ?? null;

        return is_string($email) && $email !== '' ? $email : null;
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
