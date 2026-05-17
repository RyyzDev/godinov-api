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
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
        \Illuminate\Support\Facades\DB::table('users')->truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
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
            // Sales Roles
            [
                'name'     => 'Rifki Frds',
                'email'    => 'supersales@godinov.id',
                'password' => Hash::make('password'),
                'role'     => 'super_sales',
                'alias'    => 'RifkiSales',
                'bio'      => 'Super Sales Manager',
                'is_active'=> true,
            ],
            [
                'name'     => 'Rina Wijaya',
                'email'    => 'rina.wijaya@godinov.id',
                'password' => Hash::make('password'),
                'role'     => 'sales',
                'alias'    => 'RinaSales1',
                'bio'      => 'Sales Executive',
                'is_active'=> true,
            ],
            [
                'name'     => 'Dani Pratama',
                'email'    => 'dani.pratama@godinov.id',
                'password' => Hash::make('password'),
                'role'     => 'sales',
                'alias'    => 'DaniSales2',
                'bio'      => 'Sales Executive',
                'is_active'=> true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}