<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::updateOrCreate(
        ['email' => 'sanon_taw@inteqc.com'],
        ['name' => 'Tester', 'password' => null, 'is_active' => 1]
    );
    }
}
