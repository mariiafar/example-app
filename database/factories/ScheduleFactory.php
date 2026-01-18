<?php

namespace Database\Factories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        // создадим корректное время начала/окончания
        $startHour = fake()->numberBetween(8, 16); // рабочий день 8-16 ч
        $startMinute = fake()->randomElement([0, 15, 30, 45]);

        $endHour = $startHour + fake()->numberBetween(1, 3); // минимум 1 час
        if ($endHour > 20) $endHour = 20;
        $endMinute = $startMinute;

        return [
            'master_name' => fake()->name(),
            'date' => Carbon::today()->addDays(fake()->numberBetween(0, 30)),
            'time_start' => sprintf('%02d:%02d', $startHour, $startMinute),
            'time_end' => sprintf('%02d:%02d', $endHour, $endMinute),
            'status' => fake()->randomElement(['Свободно', 'Занято']),
        ];
    }
}