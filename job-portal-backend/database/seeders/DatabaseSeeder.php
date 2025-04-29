<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Job;
use App\Models\Application;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CsvCategorySeeder::class,
            CsvUserSeeder::class,
            CsvJobSeeder::class,
            CsvApplicationSeeder::class,
        ]);
    }
}
