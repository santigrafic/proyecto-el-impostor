<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'nickname' => 'admin',
            'email' => 'admin@example.com',
            'password' => '123456',
            'games_played' => 0,
            'games_won' => 0,
            'times_impostor' => 0,   
            'role_user' => 'admin'
        ]);

        User::factory()->create([
            'name' => 'user',
            'nickname' => 'user',
            'email' => 'user@example.com',
            'password' => '123456',
            'games_played' => 0,
            'games_won' => 0,
            'times_impostor' => 0,   
            'role_user' => 'user'
        ]);

        User::factory(50)->create();

    }
}
