<?php

namespace Database\Seeders;

final class ClevernessProductSeeder extends ProductSeeder
{
    public const DEVELOPMENT_ACCOUNT_ID = 'acc_01j6g000000000000000000003';

    protected function productKey(): string
    {
        return 'cleverness';
    }
}
