<?php

namespace App\Livewire;

use App\Models\Schedule;
use App\Models\User;
use Livewire\Component;
use Carbon\Carbon;

class ScheduleBrowser extends Component
{
    public $openedMaster = null;
    public $selectedDate = null;
    public $currentMonth;
    public $selectedTimeSlot = null;

    public function mount()
    {
        $this->currentMonth = Carbon::today()->format('Y-m-01');
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
        $masters = User::where('role', 'master')->get();

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

if ($this->openedMaster && $this->selectedDate) {
    $start = Carbon::createFromTime(9, 0);
    $end = Carbon::createFromTime(19, 30);

    while ($start <= $end) {
        $timeHms = $start->format('H:i:s'); 
        $time = $start->format('H:i');      // для отображения

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

        return view('livewire.schedule-browser', [
            'masters' => $masters,
            'timeSlots' => $timeSlots,
            'availableDatesByMaster' => $availableDatesByMaster,
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