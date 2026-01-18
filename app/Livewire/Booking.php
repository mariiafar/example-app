<?php

namespace App\Livewire;

use App\Models\Application;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Booking extends Component
{
    public $services;
    public $masters;
    public $selectedService = '';
    public $master_id = '';
    public $selectedDate = '';
    public $selectedTime = '';
    public $client_name;
    public $phone;
    public $email;
    public $notes;
    
    public $showSuccessModal = false;
    public $showPaymentModal = false;
    public $showPaymentProcessing = false;
    public $bookingEndTime = '';
    public $depositAmount = 0;

    // Данные для формы оплаты
    public $cardNumber = '';
    public $cardHolder = '';
    public $cardExpiry = '';
    public $cardCvv = '';

    // Параметры маршрута
    public $route_master_id;
    public $route_date;
    public $route_time;

    // Данные для модального окна успеха
    public $bookingDetails = [];

    public function mount($master_id = null, $date = null, $time = null)
    {
        $this->masters = User::where('role', 'master')->get();
        $this->route_master_id = $master_id;
        $this->route_date = $date;
        $this->route_time = $time;

        if ($master_id) {
            $this->master_id = $master_id;
        }
        
        if ($date) {
            $this->selectedDate = $date;
        }
        
        // Загружаем услуги после установки мастера и даты, чтобы применить фильтрацию
        if ($this->master_id) {
            $this->loadServicesForMaster();
        } else {
            $this->services = collect();
        }
        
        // Устанавливаем время, если оно передано через маршрут
        if ($time) {
            $this->selectedTime = $time;
            // Вызываем метод для расчета времени окончания, если выбрана услуга
            $this->updatedSelectedTime();
        }

        if (auth()->check()) {
            $user = auth()->user();
            $this->client_name = $user->name;
            $this->email = $user->email;
        }
    }
    
    protected function loadServicesForMaster()
    {
        if ($this->master_id) {
            $master = User::find($this->master_id);
            $allServices = $master ? $master->services : collect();
            
            // Если выбраны мастер и дата, фильтруем услуги по доступности времени
            if ($this->master_id && $this->selectedDate) {
                $this->services = $allServices->filter(function ($service) {
                    return $this->isServiceAvailable($service);
                });
            } else {
                $this->services = $allServices;
            }
        } else {
            $this->services = collect();
        }
    }

    protected function isServiceAvailable($service)
    {
        if (!$this->master_id || !$this->selectedDate) {
            return true; // Если нет мастера или даты, показываем все услуги
        }

        $serviceDuration = $service->duration;
        $startHour = 9;
        $endHour = 19;
        $workEndTime = Carbon::createFromTime(19, 30);

        // Вычисляем количество необходимых 30-минутных слотов
        $requiredSlots = ceil($serviceDuration / 30);
        
        // Если требуется больше слотов, чем доступно в рабочем дне, услуга недоступна
        $maxSlotsInDay = 21; // с 9:00 до 19:30 = 21 слот по 30 минут
        if ($requiredSlots > $maxSlotsInDay) {
            return false;
        }

        // Проверяем каждый возможный стартовый слот
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                if ($hour == 19 && $minute > 0) continue;
                
                $startTime = Carbon::createFromTime($hour, $minute);
                $endTime = $startTime->copy()->addMinutes($serviceDuration);
                
                // Проверяем, не выходит ли время услуги за пределы рабочего дня
                if ($endTime->gt($workEndTime)) {
                    continue;
                }
                
                // Проверяем, что все промежуточные слоты в интервале услуги доступны
                $currentCheckTime = $startTime->copy();
                $allSlotsAvailable = true;
                $checkedSlots = 0;
                
                // Проверяем каждый 30-минутный слот в интервале услуги
                // Проверяем до тех пор, пока текущее время меньше времени окончания услуги
                while ($currentCheckTime->lt($endTime) && $allSlotsAvailable) {
                    $checkTimeHms = $currentCheckTime->format('H:i:s');
                    
                    // Проверяем, есть ли слот в расписании
                    $checkSlot = Schedule::where('master_id', $this->master_id)
                        ->where('date', $this->selectedDate)
                        ->where('time_start', $checkTimeHms)
                        ->first();
                    
                    // Слот должен существовать и иметь статус 'available'
                    if (!$checkSlot) {
                        $allSlotsAvailable = false;
                        break;
                    }
                    
                    if ($checkSlot->status !== 'available') {
                        $allSlotsAvailable = false;
                        break;
                    }
                    
                    // Проверяем, не занят ли этот слот пересекающимися записями (busy или booked)
                    // Проверяем записи, которые начинаются до или в это время и заканчиваются после этого времени
                    $isCheckBusy = Schedule::where('master_id', $this->master_id)
                        ->where('date', $this->selectedDate)
                        ->whereIn('status', ['busy', 'booked'])
                        ->where(function($query) use ($checkTimeHms) {
                            $query->where(function($q) use ($checkTimeHms) {
                                // Записи, которые начинаются до этого времени и заканчиваются после
                                $q->where('time_start', '<=', $checkTimeHms)
                                  ->where('time_end', '>', $checkTimeHms);
                            })->orWhere(function($q) use ($checkTimeHms) {
                                // Записи, которые начинаются в это время
                                $q->where('time_start', $checkTimeHms);
                            });
                        })
                        ->exists();
                    
                    if ($isCheckBusy) {
                        $allSlotsAvailable = false;
                        break;
                    }
                    
                    $checkedSlots++;
                    // Переходим к следующему 30-минутному слоту
                    $currentCheckTime->addMinutes(30);
                }
                
                // Проверяем, что проверили все необходимые слоты и все они доступны
                if ($allSlotsAvailable && $checkedSlots >= $requiredSlots) {
                    return true;
                }
            }
        }
        
        // Если не нашли ни одного доступного времени, услуга недоступна
        return false;
    }

    public function getTimeSlotsProperty()
    {
        if (!$this->master_id || !$this->selectedDate) return [];

        $slots = [];
        $startHour = 9;
        $endHour = 19;
        
        // Получаем длительность выбранной услуги (если выбрана)
        $serviceDuration = 0;
        if ($this->selectedService) {
            $service = Service::find($this->selectedService);
            $serviceDuration = $service ? $service->duration : 0;
        }

        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                if ($hour == 19 && $minute > 0) continue;
                
                $time = sprintf('%02d:%02d', $hour, $minute);
                $timeHms = $time . ':00';
                
                // Проверяем, есть ли запись в расписании для этого времени
                $slotRecord = Schedule::where('master_id', $this->master_id)
                    ->where('date', $this->selectedDate)
                    ->where('time_start', $timeHms)
                    ->first();
                
                // Если записи нет, слот недоступен
                if (!$slotRecord) {
                    $slots[] = [
                        'time' => $time,
                        'busy' => true,
                        'available' => false,
                    ];
                    continue;
                }
                
                // Проверяем, не занят ли слот
                $isBusy = Schedule::where('master_id', $this->master_id)
                    ->where('date', $this->selectedDate)
                    ->where('status', 'busy')
                    ->where('time_start', '<=', $timeHms)
                    ->where('time_end', '>', $timeHms)
                    ->exists();
                
                // Проверяем статус слота
                $isAvailable = $slotRecord->status === 'available' && !$isBusy;
                
                // Если выбрана услуга, проверяем, хватит ли времени до конца рабочего дня
                if ($isAvailable && $serviceDuration > 0) {
                    $startTime = Carbon::parse($time);
                    $endTime = $startTime->copy()->addMinutes($serviceDuration);
                    $workEndTime = Carbon::createFromTime(19, 30);
                    
                    // Проверяем, не выходит ли время услуги за пределы рабочего дня
                    if ($endTime->gt($workEndTime)) {
                        $isAvailable = false;
                    } else {
                        // Проверяем, что все промежуточные слоты в интервале услуги доступны
                        $currentCheckTime = $startTime->copy();
                        $allSlotsAvailable = true;
                        
                        while ($currentCheckTime->lt($endTime) && $allSlotsAvailable) {
                            $checkTimeHms = $currentCheckTime->format('H:i:s');
                            
                            // Проверяем, есть ли слот в расписании
                            $checkSlot = Schedule::where('master_id', $this->master_id)
                                ->where('date', $this->selectedDate)
                                ->where('time_start', $checkTimeHms)
                                ->first();
                            
                            if (!$checkSlot || $checkSlot->status !== 'available') {
                                $allSlotsAvailable = false;
                                break;
                            }
                            
                            // Проверяем, не занят ли этот слот
                            $isCheckBusy = Schedule::where('master_id', $this->master_id)
                                ->where('date', $this->selectedDate)
                                ->where('status', 'busy')
                                ->where('time_start', '<=', $checkTimeHms)
                                ->where('time_end', '>', $checkTimeHms)
                                ->exists();
                            
                            if ($isCheckBusy) {
                                $allSlotsAvailable = false;
                                break;
                            }
                            
                            // Переходим к следующему 30-минутному слоту
                            $currentCheckTime->addMinutes(30);
                        }
                        
                        $isAvailable = $allSlotsAvailable;
                    }
                }

                $slots[] = [
                    'time' => $time,
                    'busy' => !$isAvailable,
                    'available' => $isAvailable,
                ];
            }
        }

        return $slots;
    }

    public function updatedSelectedService()
    {
        $this->calculateDeposit();
        
        // Сбрасываем выбранное время, если оно стало недоступным из-за длительности услуги
        if ($this->selectedTime) {
            $slot = collect($this->timeSlots)->firstWhere('time', $this->selectedTime);
            if (!$slot || !$slot['available'] || $slot['busy']) {
                $this->selectedTime = '';
                $this->bookingEndTime = '';
                session()->flash('warning', 'Выбранное время стало недоступным для этой услуги. Пожалуйста, выберите другое время.');
            } else {
                $this->calculateEndTime();
            }
        } else {
            $this->calculateEndTime();
        }
    }

    public function updatedSelectedTime()
    {
        $this->calculateEndTime();
    }

    public function selectTimeSlot($time)
    {
        $this->selectedTime = $time;
        $this->calculateEndTime();
    }
    
    public function updatedMasterId()
    {
        // Загружаем услуги для выбранного мастера
        $this->loadServicesForMaster();
        
        // Сбрасываем выбранную услугу, если она не доступна у нового мастера
        if ($this->selectedService) {
            $serviceExists = $this->services->contains('id', $this->selectedService);
            if (!$serviceExists) {
                $this->selectedService = '';
                $this->bookingEndTime = '';
                $this->depositAmount = 0;
            }
        }
    }

    public function updatedSelectedDate()
    {
        // При изменении даты перезагружаем услуги с учетом доступности времени
        if ($this->master_id) {
            $this->loadServicesForMaster();
            
            // Сбрасываем выбранную услугу, если она стала недоступна
            if ($this->selectedService) {
                $serviceExists = $this->services->contains('id', $this->selectedService);
                if (!$serviceExists) {
                    $this->selectedService = '';
                    $this->bookingEndTime = '';
                    $this->depositAmount = 0;
                    session()->flash('warning', 'Выбранная услуга недоступна на эту дату. Пожалуйста, выберите другую услугу.');
                }
            }
        }
    }

    protected function calculateEndTime()
    {
        if ($this->selectedTime && $this->selectedService) {
            $service = Service::find($this->selectedService);
            if ($service) {
                $this->bookingEndTime = Carbon::parse($this->selectedTime)
                    ->addMinutes($service->duration)
                    ->format('H:i');
            }
        } else {
            $this->bookingEndTime = '';
        }
    }

    protected function calculateDeposit()
    {
        if ($this->selectedService) {
            $service = Service::find($this->selectedService);
            $this->depositAmount = $service ? $service->price : 0;
        } else {
            $this->depositAmount = 0;
        }
    }

    public function proceedToPayment()
    {
        $this->validate([
            'client_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email',
            'selectedService' => 'required|exists:services,id',
            'master_id' => 'required|exists:users,id',
            'selectedDate' => 'required|date|after_or_equal:today',
            'selectedTime' => 'required',
        ]);

        $this->calculateDeposit();
        $this->showPaymentModal = true;
    }

    public function processPayment()
    {
        // Упрощенная валидация для тестирования
        $this->validate([
            'cardNumber' => 'required|min:16',
            'cardHolder' => 'required',
            'cardExpiry' => 'required',
            'cardCvv' => 'required|min:3',
        ]);

        // Показываем индикатор загрузки
        $this->showPaymentProcessing = true;

        // Используем задержку для имитации обработки платежа
        $this->dispatch('payment-processing');
    }

    // Обработчик события после задержки
    public function completePayment()
    {
        try {
            // Создаем бронирование и получаем детали
            $this->createBooking();
            
            // Успешная оплата
            $this->showPaymentProcessing = false;
            $this->showPaymentModal = false;
            $this->showSuccessModal = true;

        } catch (\Exception $e) {
            $this->showPaymentProcessing = false;
            session()->flash('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    protected function createBooking()
    {
        $service = Service::find($this->selectedService);
        if (!$service) {
            throw new \Exception('Услуга не найдена');
        }

        $duration = $service->duration;
        $start = Carbon::parse($this->selectedTime);
        $end = $start->copy()->addMinutes($duration);

        // Проверка занятости
        $busy = Schedule::where('master_id', $this->master_id)
            ->where('date', $this->selectedDate)
            ->where('status', 'busy')
            ->where(function($q) use ($start, $end) {
                $q->where('time_start', '<', $end->format('H:i:s'))
                  ->where('time_end', '>', $start->format('H:i:s'));
            })
            ->exists();

        if ($busy) {
            throw new \Exception('Это время уже занято. Пожалуйста, выберите другое время.');
        }

        // Сначала создаём заявку
        $application = Application::create([
            'date' => $this->selectedDate,
            'time' => $this->selectedTime,
            'time_end' => $end->format('H:i'),
            'client_name' => $this->client_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'service_id' => $this->selectedService,
            'master_id' => $this->master_id,
            'notes' => $this->notes,
            'user_id' => auth()->id(),
            'status' => 'confirmed',
            'source' => 'website',
            'deposit' => $this->depositAmount,
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'payment_id' => 'TEST-' . uniqid(),
        ]);

        // Затем создаём запись в расписании с ссылкой на заявку
        Schedule::create([
            'master_id' => $this->master_id,
            'date' => $this->selectedDate,
            'time_start' => $start->format('H:i:s'),
            'time_end' => $end->format('H:i:s'),
            'status' => 'busy',
            'application_id' => $application->id,
        ]);

        // Сохраняем детали для показа в модальном окне успеха
        $this->bookingDetails = [
            'service_name' => $service->name,
            'service_price' => $service->price,
            'master_name' => $this->masters->firstWhere('id', $this->master_id)->name ?? '',
            'date' => $this->selectedDate,
            'time' => $this->selectedTime,
            'time_end' => $end->format('H:i'),
            'client_name' => $this->client_name,
            'deposit_amount' => $this->depositAmount,
        ];

        // Сбрасываем данные карты
        $this->reset(['cardNumber', 'cardHolder', 'cardExpiry', 'cardCvv']);
        $this->dispatch('schedule-updated');
    }

    public function updatedCardNumber($value)
    {
        // Очищаем от всех нецифровых символов
        $cleaned = preg_replace('/\D/', '', $value);
        
        // Форматируем по 4 цифры с пробелами
        if (strlen($cleaned) > 0) {
            $chunks = str_split($cleaned, 4);
            $formatted = implode(' ', $chunks);
            // Ограничиваем до 19 символов (16 цифр + 3 пробела)
            $this->cardNumber = substr($formatted, 0, 19);
        } else {
            $this->cardNumber = '';
        }
    }

    public function updatedCardExpiry($value)
    {
        // Очищаем от всех нецифровых символов
        $cleaned = preg_replace('/\D/', '', $value);
        
        if (strlen($cleaned) >= 2) {
            $month = substr($cleaned, 0, 2);
            $year = substr($cleaned, 2, 2);
            
            // Автоматически добавляем пробелы и слеш
            if (strlen($cleaned) >= 4) {
                $this->cardExpiry = $month . ' / ' . $year;
            } else {
                $this->cardExpiry = $month . ' / ';
            }
        } else {
            $this->cardExpiry = $cleaned;
        }
    }

    public function updatedCardCvv($value)
    {
        // Очищаем и ограничиваем 3 цифрами
        $cleaned = preg_replace('/\D/', '', $value);
        $this->cardCvv = substr($cleaned, 0, 3);
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->showPaymentProcessing = false;
        $this->reset(['cardNumber', 'cardHolder', 'cardExpiry', 'cardCvv']);
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->resetForm();
        return redirect('/schedule-browser');
    }

    protected function resetForm()
    {
        // Сбрасываем основные поля формы
        $this->reset([
            'selectedService',
            'master_id', 
            'selectedDate',
            'selectedTime',
            'client_name',
            'phone',
            'email',
            'notes',
            'depositAmount',
            'bookingEndTime',
            'bookingDetails'
        ]);
    }

    public function render()
    {
        return view('livewire.booking');
    }
}