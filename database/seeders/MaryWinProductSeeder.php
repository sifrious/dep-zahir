<?php

namespace Database\Seeders;

final class MaryWinProductSeeder extends ProductSeeder
{
    public const DEVELOPMENT_ACCOUNT_ID = 'acc_01j6g000000000000000000002';

    protected function productKey(): string
    {
        return 'mary-win';
    }
}
