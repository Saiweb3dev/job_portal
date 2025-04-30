<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
// To Create a new Admin
// php artisan create:admin "Admin Name" "admin@example.com" "password"
class CreateAdminUser extends Command
{
    protected $signature = 'create:admin {name} {email} {password}';
    protected $description = 'Create an admin user';

    public function handle()
    {
        $validator = Validator::make([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => $this->argument('password'),
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $user = User::create([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => Hash::make($this->argument('password')),
            'role' => 'admin',
        ]);

        $this->info("Admin user created successfully: {$user->email}");
        return 0;
    }
}
