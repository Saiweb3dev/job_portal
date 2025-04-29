<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CsvCategorySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/categories.csv');
        $file = fopen($path, 'r');
        fgetcsv($file); // skip header

        while (($row = fgetcsv($file)) !== false) {
            Category::create([
                'name' => $row[0],
            ]);
        }

        fclose($file);
    }
}
