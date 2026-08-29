<?php

use App\Models\ServiceCaller;
use App\Models\ServiceCredential;
use App\Services\ServiceCredentials;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('zahir:caller-credential:issue {caller} {--label=rotation} {--expires=}', function (ServiceCredentials $credentials): int {
    $key = (string) $this->argument('caller');
    $caller = ServiceCaller::query()->firstOrCreate(['key' => $key], ['name' => $key]);
    $expires = $this->option('expires');
    $expiresAt = is_string($expires) && $expires !== '' ? new DateTimeImmutable($expires) : null;
    $token = $credentials->issue($caller, (string) $this->option('label'), $expiresAt);
    $this->warn('Copy this credential now. Only its one-way hash is stored:');
    $this->line($token);

    return 0;
})->purpose('Issue an overlapping service caller credential');

Artisan::command('zahir:caller-credential:revoke {credential}', function (): int {
    $credential = ServiceCredential::query()->findOrFail((string) $this->argument('credential'));
    $credential->forceFill(['revoked_at' => now()])->save();
    $this->info("Credential {$credential->id} revoked.");

    return 0;
})->purpose('Immediately revoke a service caller credential');
