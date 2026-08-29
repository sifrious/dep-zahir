<?php

namespace App\Services;

use App\Models\ServiceCaller;
use App\Models\ServiceCredential;
use Illuminate\Support\Str;

final class ServiceCredentials
{
    public function issue(ServiceCaller $caller, string $label, ?\DateTimeInterface $expiresAt = null): string
    {
        $secret = Str::random(64);
        $credential = $caller->credentials()->create([
            'label' => $label,
            'secret_hash' => password_hash($secret, PASSWORD_DEFAULT),
            'expires_at' => $expiresAt,
        ]);

        return "zhr.{$credential->id}.{$secret}";
    }

    public function authenticate(?string $token): ?ServiceCredential
    {
        if (! is_string($token) || preg_match('/^zhr\.([A-Za-z0-9]{26})\.([A-Za-z0-9]{64})$/', $token, $matches) !== 1) {
            return null;
        }

        $credential = ServiceCredential::query()->with('caller')->find($matches[1]);
        if ($credential === null || $credential->revoked_at !== null || $credential->caller->disabled_at !== null
            || ($credential->expires_at !== null && $credential->expires_at->isPast())
            || ! password_verify($matches[2], $credential->secret_hash)) {
            return null;
        }

        $credential->forceFill(['last_used_at' => now()])->save();

        return $credential;
    }
}
