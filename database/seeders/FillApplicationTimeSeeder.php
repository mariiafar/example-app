<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Application;
use Illuminate\Database\Seeder;

class FillApplicationTimeSeeder extends Seeder
{
    public function run(): void
    {
        $times = ['10:00', '11:30', '13:00', '14:30', '16:00', '17:30'];
        $applications = Application::all();

        foreach ($applications as $app) {
            $app->update([
                'time' => $times[array_rand($times)],
            ]);
        }
    }
}