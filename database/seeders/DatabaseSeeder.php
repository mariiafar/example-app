<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       // \App\Models\User::factory(40)->create();

       // \App\Models\Service::factory(10)->create();

       // \App\Models\Application::factory(10)->create();

      // \App\Models\Schedule::factory(10)->create();

      // \App\Models\Review::factory()->count(10)->create();

      // Создадим 1 администратора
User::factory()->create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('admin'), // или любой другой
    'role' => 'admin',
]);

// Создадим 10 мастеров
User::factory(10)->create([
    'role' => 'master',
]);

User::factory()->create([
    'name' => 'Master',
    'email' => 'master@example.com',
    'password' => Hash::make('master'), // или любой другой
    'role' => 'master',
]);

// Создадим 10 клиентов
User::factory(10)->create([
    'role' => 'client',
]);

User::factory()->create([
    'name' => 'Client',
    'email' => 'client@example.com',
    'password' => Hash::make('client'), // или любой другой
    'role' => 'client',
]);


        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }

}
