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
        $this->services = Service::all();
        $this->masters = User::where('role', 'master')->get();
        $this->route_master_id = $master_id;
        $this->route_date = $date;
        $this->route_time = $time;

        if ($master_id) $this->master_id = $master_id;
        if ($date) $this->selectedDate = $date;
        if ($time) {
            $this->selectedTime = $time;
            $this->updatedSelectedTime();
        }

        if (auth()->check()) {
            $user = auth()->user();
            $this->client_name = $user->name;
            $this->email = $user->email;
            
        }
    }

    public function getTimeSlotsProperty()
    {
        if (!$this->master_id || !$this->selectedDate) return [];

        $slots = [];
        $startHour = 9;
        $endHour = 19;

        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                if ($hour == 19 && $minute > 0) continue;
                
                $time = sprintf('%02d:%02d', $hour, $minute);
                $isBusy = Schedule::where('master_id', $this->master_id)
                    ->where('date', $this->selectedDate)
                    ->where(function($query) use ($time) {
                        $query->where('time_start', '<=', $time)
                              ->where('time_end', '>', $time);
                    })
                    ->where('status', 'busy')
                    ->exists();

                $slots[] = [
                    'time' => $time,
                    'busy' => $isBusy,
                ];
            }
        }

        return $slots;
    }

    public function selectTimeSlot($time)
    {
        $slot = collect($this->timeSlots)->firstWhere('time', $time);
        if ($slot && !$slot['busy']) {
            $this->selectedTime = $time;
            $this->calculateEndTime();
        }
    }

    public function updatedSelectedService()
    {
        $this->calculateEndTime();
        $this->calculateDeposit();
    }

    public function updatedSelectedTime()
    {
        $this->calculateEndTime();
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