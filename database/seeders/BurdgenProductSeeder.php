<?php

namespace Database\Seeders;

final class BurdgenProductSeeder extends ProductSeeder
{
    public const DEVELOPMENT_ACCOUNT_ID = 'acc_01j6g000000000000000000001';

    protected function productKey(): string
    {
        return 'burdgen';
    }
}
