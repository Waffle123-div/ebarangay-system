<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Admin User
        User::create([
            'user_id' => 'USR-00001',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
            'birthdate' => '1990-01-01',
            'gender' => 'Male',
        ]);

        // Seed Resident User
        User::create([
            'user_id' => 'USR-00002',
            'name' => 'Resident User',
            'email' => 'resident@example.com',
            'password' => Hash::make('password'),
            'role' => 'Resident',
            'status' => 'active',
            'birthdate' => '1995-05-05',
            'gender' => 'Female',
        ]);

        // Seed Official Document Types and Templates
        $this->call(DocumentTypeSeeder::class);
    }
}
