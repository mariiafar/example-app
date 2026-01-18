<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 
 */
class ServiceFactory extends Factory
{
    
    protected $model = Service::class; 
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'type' => fake()->randomElement(['Татуировка', 'Пирсинг', 'Лазерное удаление']),
            'price' => fake()->numberBetween(1000, 30000),
            'duration' => fake()->numberBetween(30, 360),
            'description' => fake()->sentence(10),
        ];
    }


}
