<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Finance',
                'email'    => 'finance@example.com',
                'password' => Hash::make('password123'),
                'role'     => 'finance',
                'alias'    => 'finance',
                'is_active'=> true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}