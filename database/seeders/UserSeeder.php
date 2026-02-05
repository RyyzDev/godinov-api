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
                'name'     => 'Sang UI/UX',
                'email'    => 'uiux@godinov.id',
                'alias'    => 'uiux',
                'password' => Hash::make('password'),
                'role'     => 'uiux',
                'bio'    => ' ',
                'is_active'=> true,
            ],
            [
                'name'     => 'M. Rifki Firdaus',
                'email'    => 'frontend@godinov.id',
                'alias'    => 'Rifkifrds',
                'password' => Hash::make('password'),
                'role'     => 'frontend',
                'bio'      => 'bear',
                'is_active'=> true,
            ],
            [
                'name'     => 'Fachri Akbar K.',
                'email'    => 'backend@example.com',                
                'password' => Hash::make("asdfghjkl;'"),
                'role'     => 'backend',
                'alias'    => 'RyyzDev',
                'bio'      => 'Backend nich',
                'is_active'=> true,
            ],
            [
                'name'     => 'Sang Project Manager',
                'email'    => 'pm@godinov.id',                
                'password' => Hash::make('password'),
                'role'     => 'pm',
                'alias'    => 'pm',
                'bio'      => 'PM',
                'is_active'=> true,
            ],
            [
                'name'     => 'Ahmad Ghifari Z.',
                'email'    => 'finance@godinov.id',                
                'password' => Hash::make('password'),
                'role'     => 'pm',
                'alias'    => 'pm',
                'bio'      => 'PM',
                'is_active'=> true,
            ],
            // Opsional: Akun Admin untuk manage semua
            [
                'name'     => 'Super User',
                'email'    => 'superuser@godinov.id',
                'password' => Hash::make('admingodinov'),
                'role'     => 'admin',
                'alias'    => 'Super User',
                'is_active'=> true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}