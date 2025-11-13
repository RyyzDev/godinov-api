<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Inbox;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        DB::table('inboxes')->truncate();
        $inbox = [

        [
            'name' => 'Fachri Akbar Kutubi',
            'email' => 'dev@fachryyz.com',
            'contact' => '0813232342',
            'company' => 'Godinov Indonesia',
            'address' => 'Jl. KH Mursan RT03/03',
            'description' => 'saya ingin membuat website landing page',
            ],
            [
            'name' => 'Dimas Arya Dinata',
            'email' => 'dev@dimas.com',
            'contact' => '0813232342',
            'company' => 'Godinov Indonesia',
            'address' => 'Jl. KH Mursan RT03/03',
            'description' => 'saya ingin membuat website landing page',
            ],
            [
            'name' => 'Muhammad Rifki Firdaus',
            'email' => 'dev@rifki.com',
            'contact' => '0813232342',
            'company' => 'Godinov Indonesia',
            'address' => 'Jl. KH Mursan RT03/03',
            'description' => 'saya ingin membuat website landing page',
            ],
        ];

        DB::table('inboxes')->insert($inbox);

    }
}
