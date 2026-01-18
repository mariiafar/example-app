<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Service;

class MasterServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем всех мастеров
        $masters = User::where('role', 'master')->get();
        
        // Получаем все услуги
        $services = Service::all();
        
        // Если нет услуг или мастеров, выходим
        if ($services->isEmpty() || $masters->isEmpty()) {
            $this->command->warn('Нет услуг или мастеров для связывания. Сначала создайте услуги и мастеров.');
            return;
        }
        
        // Для каждого мастера привязываем случайные услуги
        foreach ($masters as $master) {
            // Каждый мастер получает от 2 до всех доступных услуг
            $servicesCount = rand(2, $services->count());
            
            // Выбираем случайные услуги
            $randomServices = $services->random(min($servicesCount, $services->count()));
            
            // Привязываем услуги к мастеру
            $serviceIds = $randomServices->pluck('id')->toArray();
            $master->services()->syncWithoutDetaching($serviceIds);
            
            $this->command->info("Мастеру '{$master->name}' привязано " . count($serviceIds) . " услуг.");
        }
        
        $this->command->info('Связывание мастеров с услугами завершено!');
    }
}
