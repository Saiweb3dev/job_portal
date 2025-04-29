<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CsvUserSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/users.csv');
        $file = fopen($path, 'r');
        fgetcsv($file); // skip header

        while (($row = fgetcsv($file)) !== false) {
            User::create([
                'name' => $row[0],
                'email' => $row[1],
                'password' => Hash::make($row[2]),
                'role' => $row[3],
            ]);
        }

        fclose($file);
    }
}
