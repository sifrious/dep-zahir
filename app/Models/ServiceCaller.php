<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'disabled_at'])]
class ServiceCaller extends Model
{
    use HasUlids;

    public function credentials(): HasMany
    {
        return $this->hasMany(ServiceCredential::class);
    }

    protected function casts(): array
    {
        return ['disabled_at' => 'immutable_datetime'];
    }
}
