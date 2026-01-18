<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Schedule;
use Livewire\WithPagination;
use Carbon\Carbon;

class Schedules extends Component
{
    use WithPagination;

    public $master_id, $date, $time_start, $time_end, $status;
    public $schedule_id;
    public $isOpen = false;
    public $search = '';
    public $filterStatus = 'all';
    public $sortField = 'date';
    public $sortDirection = 'asc';
    public $selectedSlotForMenu = null; // Для выпадающего меню

    protected $rules = [
        'master_id' => 'required|exists:users,id',
        'date' => 'required|date',
        'time_start' => 'required',
        'time_end' => 'required',
        'status' => 'required|in:available,busy,booked,canceled',
    ];

    protected $messages = [
        'master_id.required' => 'Выберите мастера',
        'date.required' => 'Выберите дату',
        'time_start.required' => 'Введите время начала',
        'time_end.required' => 'Введите время окончания',
        'status.required' => 'Выберите статус',
    ];

    public function render()
    {
        $query = Schedule::with('master');

        if (auth()->user()->role === 'master') {
            $query->where('master_id', auth()->id());
        }

        if ($this->search) {
            // Для мастера поиск не нужен, так как он видит только свои записи
            if (auth()->user()->role !== 'master') {
                $query->whereHas('master', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            }
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        // Основная сортировка по выбранному полю
        $query->orderBy($this->sortField, $this->sortDirection);
        
        // Вторичная сортировка для более точного упорядочивания
        if ($this->sortField !== 'time_start') {
            $query->orderBy('time_start');
        }
        
        $schedules = $query->paginate(10);

        // Для редактирования используем простой список временных слотов
        $timeSlotsList = $this->getTimeSlots();
        // Для создания используем массив с данными о слотах
        $timeSlots = $this->getTimeSlotsForDate();

        return view('livewire.schedules', compact('schedules', 'timeSlots', 'timeSlotsList'));
    }

    public function getTimeSlots()
    {
        $slots = [];
        for ($hour = 9; $hour <= 19; $hour++) {
            $slots[] = sprintf('%02d:00', $hour);
            if ($hour < 19) {
                $slots[] = sprintf('%02d:30', $hour);
            }
        }
        return $slots;
    }

    public function getTimeSlotsForDate()
    {
        $timeSlots = [];

        if ($this->master_id && $this->date) {
            $start = Carbon::createFromTime(9, 0);
            $end = Carbon::createFromTime(19, 30);

            while ($start <= $end) {
                $timeHms = $start->format('H:i:s');
                $time = $start->format('H:i');

                $slotRecord = Schedule::where('master_id', $this->master_id)
                    ->where('date', $this->date)
                    ->where('time_start', $timeHms)
                    ->first();

                // Проверяем, не перекрывается ли этот слот занятым временем из других записей (только busy)
                $isBooked = Schedule::where('master_id', $this->master_id)
                    ->where('date', $this->date)
                    ->where('status', 'busy')
                    ->where('time_start', '<=', $timeHms)
                    ->where('time_end', '>', $timeHms)
                    ->exists();

                // Проверяем пересечение с booked статусами отдельно
                $isBookedOverlap = Schedule::where('master_id', $this->master_id)
                    ->where('date', $this->date)
                    ->where('status', 'booked')
                    ->where('time_start', '<=', $timeHms)
                    ->where('time_end', '>', $timeHms)
                    ->exists();

                if ($slotRecord) {
                    // Если слот существует, используем его статус
                    $isAvailable = $slotRecord->status === 'available' && !$isBooked && !$isBookedOverlap;
                    $isBusy = $slotRecord->status === 'busy' || $isBooked;
                    $isBookedStatus = $slotRecord->status === 'booked' || ($isBookedOverlap && $slotRecord->status !== 'busy');
                    $isCanceled = $slotRecord->status === 'canceled';
                } else {
                    // Если слот не существует, он не доступен и не занят
                    $isAvailable = false;
                    $isBusy = $isBooked;
                    $isBookedStatus = $isBookedOverlap;
                    $isCanceled = false;
                }

                $timeSlots[] = [
                    'time' => $time,
                    'available' => $isAvailable,
                    'busy' => $isBusy,
                    'isBooked' => $isBooked,
                    'isBookedStatus' => $isBookedStatus,
                    'isCanceled' => $isCanceled,
                    'schedule' => $slotRecord,
                ];

                $start->addMinutes(30);
            }
        }

        return $timeSlots;
    }

    public function openSlotMenu($time)
    {
        if (!$this->master_id || !$this->date) {
            session()->flash('error', 'Выберите мастера и дату');
            return;
        }

        if (!in_array(auth()->user()->role, ['admin', 'master'])) {
            abort(403);
        }

        if (auth()->user()->role === 'master' && $this->master_id != auth()->id()) {
            abort(403);
        }

        // Открываем/закрываем меню для выбранного слота
        if ($this->selectedSlotForMenu === $time) {
            $this->selectedSlotForMenu = null;
        } else {
            $this->selectedSlotForMenu = $time;
        }
    }

    public function setSlotStatus($time, $status)
    {
        if (!$this->master_id || !$this->date) {
            session()->flash('error', 'Выберите мастера и дату');
            return;
        }

        if (!in_array(auth()->user()->role, ['admin', 'master'])) {
            abort(403);
        }

        if (auth()->user()->role === 'master' && $this->master_id != auth()->id()) {
            abort(403);
        }

        if (!in_array($status, ['available', 'busy', 'booked', 'canceled', 'delete'])) {
            session()->flash('error', 'Неверный статус');
            return;
        }

        $timeForDb = $time . ':00';
        $timeEnd = Carbon::createFromFormat('H:i', $time)->addMinutes(30)->format('H:i:s');

        $slotRecord = Schedule::where('master_id', $this->master_id)
            ->where('date', $this->date)
            ->where('time_start', $timeForDb)
            ->first();

        if ($status === 'delete') {
            // Удаление слота
            if ($slotRecord) {
                $slotRecord->delete();
            }
        } else {
            if ($slotRecord) {
                // Обновляем существующий слот
                $slotRecord->update(['status' => $status]);
            } else {
                // Создаем новый слот
                Schedule::create([
                    'master_id' => $this->master_id,
                    'date' => $this->date,
                    'time_start' => $timeForDb,
                    'time_end' => $timeEnd,
                    'status' => $status,
                ]);
            }
        }

        // Закрываем меню
        $this->selectedSlotForMenu = null;
        
        session()->flash('success', 'Статус слота обновлен');
    }

    public function closeSlotMenu()
    {
        $this->selectedSlotForMenu = null;
    }

    public function createWorkingDay()
    {
        if (!$this->master_id || !$this->date) {
            session()->flash('error', 'Выберите мастера и дату');
            return;
        }

        if (!in_array(auth()->user()->role, ['admin', 'master'])) {
            abort(403);
        }

        if (auth()->user()->role === 'master' && $this->master_id != auth()->id()) {
            abort(403);
        }

        // Создаем только не созданные слоты со статусом 'available' (свободно)
        // Занятые слоты игнорируются
        $start = Carbon::createFromTime(9, 0);
        $end = Carbon::createFromTime(19, 30);
        $createdCount = 0;

        while ($start <= $end) {
            $timeStart = $start->format('H:i:s');
            
            // Проверяем, существует ли уже слот
            $existingSlot = Schedule::where('master_id', $this->master_id)
                ->where('date', $this->date)
                ->where('time_start', $timeStart)
                ->first();

            // Создаем только если слот не существует (не созданные слоты)
            if (!$existingSlot) {
                $timeEnd = $start->copy()->addMinutes(30)->format('H:i:s');
                
                Schedule::create([
                    'master_id' => $this->master_id,
                    'date' => $this->date,
                    'time_start' => $timeStart,
                    'time_end' => $timeEnd,
                    'status' => 'available',
                ]);
                $createdCount++;
            }

            $start->addMinutes(30);
        }

        if ($createdCount > 0) {
            session()->flash('success', "Создано {$createdCount} свободных слотов. Занятые слоты не изменены.");
        } else {
            session()->flash('info', 'Все слоты уже созданы. Занятые слоты не изменены.');
        }
    }




    public function create()
    {
        $this->resetInputFields();
        $this->status = 'available';
        $this->date = now()->format('Y-m-d');

        if (auth()->user()->role === 'master') {
            $this->master_id = auth()->id();
        }

        $this->isOpen = true;
    }

    public function store()
    {
        $this->validate();

        Schedule::create([
            'master_id'  => $this->master_id,
            'date'       => $this->date,
            'time_start' => $this->normalizeTime($this->time_start),
            'time_end'   => $this->normalizeTime($this->time_end),
            'status'     => $this->status ?? 'available',
        ]);

        $this->closeModal();
        session()->flash('success', 'Запись успешно создана');
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);

        if (auth()->user()->role === 'master' && $schedule->master_id !== auth()->id()) {
            abort(403);
        }

        $this->schedule_id = $id;
        $this->master_id = $schedule->master_id;
        $this->date = Carbon::parse($schedule->date)->format('Y-m-d');
        $this->time_start = Carbon::parse($schedule->time_start)->format('H:i');
        $this->time_end = Carbon::parse($schedule->time_end)->format('H:i');
        $this->status = $schedule->status;

        $this->isOpen = true;
    }

    public function update()
    {
        $this->validate();

        $schedule = Schedule::findOrFail($this->schedule_id);

        $schedule->update([
            'master_id'  => $this->master_id,
            'date'       => $this->date,
            'time_start' => $this->normalizeTime($this->time_start),
            'time_end'   => $this->normalizeTime($this->time_end),
            'status'     => $this->status,
        ]);

        $this->closeModal();
        session()->flash('success', 'Запись успешно обновлена');
    }

    

    public function delete($id)
    {
        $schedule = Schedule::findOrFail($id);

        if (auth()->user()->role === 'master' && $schedule->master_id !== auth()->id()) {
            abort(403);
        }

        $schedule->delete();
        session()->flash('success', 'Запись удалена');
    }

    public function closeModal()
    {
        $this->resetInputFields();
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->schedule_id = null;
        $this->master_id = '';
        $this->date = '';
        $this->time_start = '';
        $this->time_end = '';
        $this->status = 'available';
    }

    public function updatedMasterId()
    {
        // Сбрасываем дату при изменении мастера
        $this->date = '';
    }

    protected function normalizeTime($value)
{
    if (empty($value)) return null;

    $value = trim($value);

    if (preg_match('/^\d{1,2}:\d{2}$/', $value)) {
        return sprintf('%02d:%02d:00', ...explode(':', $value));
    }
    if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $value)) {
        return $value;
    }

    if (preg_match('/\b([01]?\d|2[0-3]):[0-5]\d(?::[0-5]\d)?\b/', $value, $m)) {
        $t = $m[0];
        if (!preg_match('/:[0-5]\d$/', $t)) $t .= ':00';
        return $t;
    }

    $ts = strtotime($value);
    if ($ts !== false) {
        return date('H:i:s', $ts);
    }

    \Log::warning('Schedules::normalizeTime failed', ['value' => $value]);
    return null;
}
}