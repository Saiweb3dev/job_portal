<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Job;

class CsvJobSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/jobs.csv');
        $file = fopen($path, 'r');
        fgetcsv($file); // skip header

        while (($row = fgetcsv($file)) !== false) {
            Job::create([
                'user_id' => $row[0],
                'category_id' => $row[1],
                'title' => $row[2],
                'description' => $row[3],
                'location' => $row[4],
                'salary' => $row[5],
                'type' => $row[6],
            ]);
        }

        fclose($file);
    }
}
