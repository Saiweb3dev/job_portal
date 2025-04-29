<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;

class CsvApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/applications.csv');
        $file = fopen($path, 'r');
        fgetcsv($file); // skip header

        while (($row = fgetcsv($file)) !== false) {
            Application::create([
                'user_id' => $row[0],
                'job_id' => $row[1],
                'cover_letter' => $row[2],
                'resume_path' => $row[3],
            ]);
        }

        fclose($file);
    }
}
