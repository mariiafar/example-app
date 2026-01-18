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

    protected $rules = [
        'master_id' => 'required|exists:users,id',
        'date' => 'required|date',
        'time_start' => 'required',
        'time_end' => 'required',
        'status' => 'required|in:available,busy,canceled',
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
            $query->whereHas('master', function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $schedules = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('time_start')
            ->paginate(10);

        $timeSlots = $this->getTimeSlots();

        return view('livewire.schedules', compact('schedules', 'timeSlots'));
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