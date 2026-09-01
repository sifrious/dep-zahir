<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LogresProductSeeder::class,
            BurdgenProductSeeder::class,
            MaryWinProductSeeder::class,
            ClevernessProductSeeder::class,
        ]);
    }
}
