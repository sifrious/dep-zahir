<?php

namespace Database\Seeders;

final class LogresProductSeeder extends ProductSeeder
{
    /** Retained for the release-rehearsal fixture, which pins this exact account. */
    public const DEVELOPMENT_ACCOUNT_ID = 'acc_01j6g000000000000000000000';

    protected function productKey(): string
    {
        return 'logres';
    }
}
