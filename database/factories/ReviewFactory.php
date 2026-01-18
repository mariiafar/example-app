<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'user_name' => $this->faker->name,
            'review_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'content' => $this->faker->paragraph(3), // 3 предложения
            'photo' => $this->faker->boolean(50)
            ? 'reviews/tattoo_review_1.jpg'
            : null,
        ];
    }
}
