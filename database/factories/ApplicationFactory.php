<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::today()->addDays(fake()->numberBetween(0, 30)),
            'client_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'service_id' => Service::factory(), // создаст новую услугу или укажи id вручную
            'source' => fake()->randomElement(['website', 'vk', 'tg', 'call', 'recommendation', 'walk_in']),
            'status' => fake()->randomElement(['new', 'confirmed', 'completed', 'canceled']),
            'master' => fake()->name(),
            'notes' => fake()->optional()->sentence(10),
            'user_id' => User::factory(), // создаст нового пользователя или укажи id вручную
        ];
    }
}