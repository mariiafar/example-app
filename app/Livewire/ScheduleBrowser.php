<?php

namespace App\Livewire;

use App\Models\Schedule;
use App\Models\User;
use Livewire\Component;
use Carbon\Carbon;

class ScheduleBrowser extends Component
{
    public $bookingMode = 'date'; // 'date' или 'master'
    public $openedMaster = null;
    public $selectedDate = null;
    public $currentMonth;
    public $selectedTimeSlot = null;
    
    // Переменные для режима выбора по дате
    public $selectedDateForDateMode = null;
    public $selectedTimeForDateMode = null;
    public $selectedMasterForDateMode = null;

    public function mount()
    {
        $this->currentMonth = Carbon::today()->format('Y-m-01');
    }
    
    public function setBookingMode($mode)
    {
        $this->bookingMode = $mode;
        // Сброс выбранных значений при переключении режима
        $this->openedMaster = null;
        $this->selectedDate = null;
        $this->selectedDateForDateMode = null;
        $this->selectedTimeForDateMode = null;
        $this->selectedMasterForDateMode = null;
    }

    public function toggleMaster($id)
    {
        if ($this->openedMaster === $id) {
            $this->openedMaster = null;
            $this->selectedDate = null;
        } else {
            $this->openedMaster = $id;
            $this->selectedDate = null;
        }
    }

    public function selectDate($date)
    {
        if ($this->bookingMode === 'master') {
            if (!$this->openedMaster) {
                return;
            }

            $hasSchedule = Schedule::where('master_id', $this->openedMaster)
                ->where('date', $date)
                ->exists();

            if ($hasSchedule) {
                $this->selectedDate = $date;
            }
        }
    }
    
    // Методы для режима выбора по дате
    public function selectDateForDateMode($date)
    {
        // Проверяем, что дата не в прошлом и есть расписание
        $hasSchedule = Schedule::where('date', $date)
            ->where('date', '>=', Carbon::today())
            ->exists();

        if ($hasSchedule) {
            $this->selectedDateForDateMode = $date;
            $this->selectedTimeForDateMode = null;
            $this->selectedMasterForDateMode = null;
        }
    }
    
    public function selectTimeForDateMode($time)
    {
        if (!$this->selectedDateForDateMode) {
            return;
        }
        
        $this->selectedTimeForDateMode = $time;
        $this->selectedMasterForDateMode = null;
    }
    
    public function selectMasterForDateMode($masterId)
    {
        if (!$this->selectedDateForDateMode || !$this->selectedTimeForDateMode) {
            return;
        }
        
        $timeForDb = $this->selectedTimeForDateMode . ':00';
        
        // Проверяем, есть ли запись в расписании для этого мастера, даты и времени
        $slotRecord = Schedule::where('master_id', $masterId)
            ->where('date', $this->selectedDateForDateMode)
            ->where('time_start', $timeForDb)
            ->first();

        // Если записи нет, мастер не работает в это время
        if (!$slotRecord) {
            session()->flash('error', 'Мастер не работает в это время');
            return;
        }

        // Проверяем, не занято ли это время (пересекающиеся busy слоты)
        $isBooked = Schedule::where('master_id', $masterId)
            ->where('date', $this->selectedDateForDateMode)
            ->where('status', 'busy')
            ->where('time_start', '<=', $timeForDb)
            ->where('time_end', '>', $timeForDb)
            ->exists();

        // Слот доступен только если статус 'available' и время не занято
        $isAvailable = $slotRecord->status === 'available' && !$isBooked;

        if ($isAvailable) {
            $this->selectedMasterForDateMode = $masterId;
            
            // Перенаправление на страницу бронирования
            if (auth()->check() && auth()->user()->role === 'client') {
                return redirect()->route('booking', [
                    'master_id' => $masterId,
                    'date' => $this->selectedDateForDateMode,
                    'time' => $this->selectedTimeForDateMode,
                ]);
            }
            
            return redirect()->route('login');
        } else {
            session()->flash('error', 'Это время уже занято');
        }
    }

    public function previousMonth()
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)
            ->subMonth()
            ->format('Y-m-01');
    }

    public function nextMonth()
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)
            ->addMonth()
            ->format('Y-m-01');
    }

    public function render()
    {
        $masters = User::where('role', 'master')->with('services')->get();

        $availableDatesByMaster = [];
        foreach ($masters as $master) {
            $availableDatesByMaster[$master->id] = Schedule::where('master_id', $master->id)
                ->where('date', '>=', Carbon::today())
                ->pluck('date')
                ->unique()
                ->map(fn($date) => $date->format('Y-m-d'))
                ->toArray();
        }

        $timeSlots = [];
        $timeSlotsForDateMode = [];
        $availableMastersForDateMode = [];

        // Режим выбора по мастеру
        if ($this->bookingMode === 'master' && $this->openedMaster && $this->selectedDate) {
            $start = Carbon::createFromTime(9, 0);
            $end = Carbon::createFromTime(19, 30);

            while ($start <= $end) {
                $timeHms = $start->format('H:i:s'); 
                $time = $start->format('H:i');

                $slotRecord = Schedule::where('master_id', $this->openedMaster)
                    ->where('date', $this->selectedDate)
                    ->where('time_start', $timeHms)
                    ->first();

                $isBooked = Schedule::where('master_id', $this->openedMaster)
                    ->where('date', $this->selectedDate)
                    ->where('status', 'busy')
                    ->where('time_start', '<=', $timeHms)
                    ->where('time_end', '>', $timeHms)
                    ->exists();

                if ($slotRecord) {
                    $isAvailable = $slotRecord->status === 'available' && !$isBooked;
                    $isBusy = $slotRecord->status === 'busy' || $isBooked;
                } else {
                    $isAvailable = !$isBooked;
                    $isBusy = $isBooked;
                }

                $timeSlots[] = [
                    'time' => $time,
                    'available' => $isAvailable,
                    'busy' => $isBusy,
                    'isBooked' => $isBooked,   
                    'schedule' => $slotRecord,
                ];

                $start->addMinutes(30);
            }
        }

        // Режим выбора по дате
        $allAvailableDates = [];
        if ($this->bookingMode === 'date') {
            // Получаем все доступные даты
            $allAvailableDates = Schedule::where('date', '>=', Carbon::today())
                ->pluck('date')
                ->unique()
                ->map(fn($date) => $date->format('Y-m-d'))
                ->toArray();
            
            // Если выбрана дата, получаем доступные временные слоты
            if ($this->selectedDateForDateMode) {
                $start = Carbon::createFromTime(9, 0);
                $end = Carbon::createFromTime(19, 30);

                while ($start <= $end) {
                    $timeHms = $start->format('H:i:s'); 
                    $time = $start->format('H:i');

                    // Проверяем, есть ли хотя бы один свободный мастер на это время
                    $availableMastersCount = Schedule::where('date', $this->selectedDateForDateMode)
                        ->where('time_start', $timeHms)
                        ->where('status', 'available')
                        ->count();

                    // Проверяем, есть ли занятые слоты
                    $isBooked = Schedule::where('date', $this->selectedDateForDateMode)
                        ->where('status', 'busy')
                        ->where('time_start', '<=', $timeHms)
                        ->where('time_end', '>', $timeHms)
                        ->exists();

                    $hasAnySchedule = Schedule::where('date', $this->selectedDateForDateMode)
                        ->where('time_start', $timeHms)
                        ->exists();

                    $isAvailable = $hasAnySchedule && $availableMastersCount > 0 && !$isBooked;

                    $timeSlotsForDateMode[] = [
                        'time' => $time,
                        'available' => $isAvailable,
                        'busy' => $isBooked || ($hasAnySchedule && $availableMastersCount === 0),
                        'isBooked' => $isBooked,
                    ];

                    $start->addMinutes(30);
                }
            }
            
            // Если выбраны дата и время, получаем доступных мастеров
            if ($this->selectedDateForDateMode && $this->selectedTimeForDateMode) {
                $timeForDb = $this->selectedTimeForDateMode . ':00';
                
                foreach ($masters as $master) {
                    // Проверяем, есть ли запись в расписании для этого мастера, даты и времени
                    $slotRecord = Schedule::where('master_id', $master->id)
                        ->where('date', $this->selectedDateForDateMode)
                        ->where('time_start', $timeForDb)
                        ->first();

                    // Если записи нет, мастер не работает в это время
                    if (!$slotRecord) {
                        continue;
                    }

                    // Проверяем, не занято ли это время (пересекающиеся busy слоты)
                    $isBooked = Schedule::where('master_id', $master->id)
                        ->where('date', $this->selectedDateForDateMode)
                        ->where('status', 'busy')
                        ->where('time_start', '<=', $timeForDb)
                        ->where('time_end', '>', $timeForDb)
                        ->exists();

                    // Слот доступен только если статус 'available' и время не занято
                    $isAvailable = $slotRecord->status === 'available' && !$isBooked;

                    if ($isAvailable) {
                        $availableMastersForDateMode[] = [
                            'id' => $master->id,
                            'name' => $master->name,
                            'available' => true,
                        ];
                    }
                }
            }
        }

        return view('livewire.schedule-browser', [
            'masters' => $masters,
            'timeSlots' => $timeSlots,
            'timeSlotsForDateMode' => $timeSlotsForDateMode,
            'availableMastersForDateMode' => $availableMastersForDateMode,
            'availableDatesByMaster' => $availableDatesByMaster,
            'allAvailableDates' => $allAvailableDates,
        ]);
    }

   public function selectTimeSlot($time)
{
    if (!$this->openedMaster || !$this->selectedDate) {
        session()->flash('error', 'Выберите мастера и дату');
        return;
    }

    $timeForDb = $time . ':00';

    $isAvailable = Schedule::where('master_id', $this->openedMaster)
        ->where('date', $this->selectedDate)
        ->where('time_start', $timeForDb)
        ->where('status', 'available')
        ->exists();

    $isBooked = Schedule::where('master_id', $this->openedMaster)
        ->where('date', $this->selectedDate)
        ->where('status', 'busy')
        ->where('time_start', '<=', $timeForDb)
        ->where('time_end', '>', $timeForDb)
        ->exists();

    $anyRecord = Schedule::where('master_id', $this->openedMaster)
        ->where('date', $this->selectedDate)
        ->where('time_start', $timeForDb)
        ->exists();


    if (($isAvailable && !$isBooked) || (!$anyRecord && !$isBooked)) {
        
        if (auth()->check() && auth()->user()->role === 'client') {
            return redirect()->route('booking', [
                'master_id' => $this->openedMaster,
                'date' => $this->selectedDate,
                'time' => $time,
            ]);
        }

        
        return redirect()->route('login');
    } else {
        session()->flash('error', 'Это время уже занято');
    }
}

protected function updateScheduleSlot($masterId, $date, $time)
{

    Schedule::where('master_id', $masterId)
        ->where('date', $date)
        ->where('time_start', $time)
        ->update(['status' => 'busy']);
    
    $this->dispatch('schedule-updated');
}

    public function getListeners()
{
    return [
        'schedule-updated' => '$refresh',
    ];
}

    public function createWorkingDay($masterId, $date)
    {
        if (!in_array(auth()->user()->role, ['admin', 'master'])) {
            abort(403);
        }

        $existingDay = Schedule::where('master_id', $masterId)
            ->where('date', $date)
            ->exists();

        if ($existingDay) {
            session()->flash('error', 'Рабочий день уже создан');
            return;
        }

        $start = Carbon::createFromTime(9, 0);
        $end = Carbon::createFromTime(19, 30);

        while ($start <= $end) {
            $timeStart = $start->format('H:i:s');
            $timeEnd = $start->copy()->addMinutes(30)->format('H:i:s');

            Schedule::create([
                'master_id' => $masterId,
                'date' => $date,
                'time_start' => $timeStart,
                'time_end' => $timeEnd,
                'status' => 'available',
            ]);

            $start->addMinutes(30);
        }

        session()->flash('success', 'Рабочий день успешно создан');

        $this->openedMaster = $masterId;
        $this->selectedDate = $date;
    }
}