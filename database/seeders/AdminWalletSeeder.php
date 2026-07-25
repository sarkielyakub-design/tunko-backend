<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
{
    $this->call([
        AdminWalletSeeder::class,
    ]);
}
}
