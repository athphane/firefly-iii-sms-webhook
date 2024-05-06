<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultUsersSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate([
            'email' => 'admin@example.com'
        ], [
            'name' => 'Admin',
            'password' => bcrypt('password')
        ]);
    }
}
