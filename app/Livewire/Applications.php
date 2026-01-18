<?php

namespace App\Livewire;

use App\Models\Application;
use App\Models\Service;
use App\Models\User;
use Livewire\Component;
use App\Models\Schedule;
use Livewire\WithPagination;
use Carbon\Carbon;

class Applications extends Component
{
    use WithPagination;
    
    public $date, $source, $client_name, $phone, $email, $service_id, $schedule_id, $notes, $status;
    public $application_id;
    public $isOpen = false;
    public $search = '';
    public $sortField = 'date';
    public $sortDirection = 'desc';
    public $filterStatus = 'all';
    public $time;
    public $master_id;
    public $depositAmount;
    

    
    public const STATUSES = [
        'new' => 'Новая',
        'confirmed' => 'Подтверждена',
        'completed' => 'Завершена',
        'canceled' => 'Отменена'
    ];

    
    public const SOURCES = [
        'website' => 'Сайт',
        'vk' => 'Вконтакте',
        'tg' => 'Телеграм',
        'call' => 'Звонок',
        'recommendation' => 'Рекомендация',
        'walk_in' => 'Оффлайн визит'
    ];

    public function render()
    {
        return view('livewire.applications', [
            'applications' => Application::with(['service', 'user', 'master'])
                ->when($this->search, function ($query) {
                    $query->where('client_name', 'like', '%'.$this->search.'%')
                          ->orWhere('phone', 'like', '%'.$this->search.'%')
                          ->orWhere('email', 'like', '%'.$this->search.'%');
                })
                ->when($this->filterStatus !== 'all', function ($query) {
                    $query->where('status', $this->filterStatus);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
            'schedules' => Schedule::where('status', false)->get(),
            'services' => Service::all(),
            'statuses' => self::STATUSES,
            'sources' => self::SOURCES,
            'timeSlots' => $this->getTimeSlots(),
        ]);
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


    protected function formatTimeForDisplay($time)
{
    if (!$time) return '';
    
    
    if (is_string($time) && strlen($time) === 8) {
        return substr($time, 0, 5);
    }
    
    return $time;
}

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    protected $rules = [
        'date' => 'required|date|after_or_equal:today',
        'time' => 'required|date_format:H:i',
        'client_name' => 'required|min:3',
        'phone' => 'required',
        'email' => 'nullable|email',
        'service_id' => 'required|exists:services,id',
        'source' => 'required|in:website,vk,tg,call,recommendation,walk_in',
        'status' => 'required|in:new,confirmed,completed,canceled',
        'master_id' => 'required|exists:users,id',
        'notes' => 'nullable|max:500'
    ];

    protected $messages = [
        'date.required' => 'Дата обязательна',
        'date.after_or_equal' => 'Дата должна быть сегодня или позже',
        'time.required' => 'Время обязательно',
        'client_name.required' => 'Имя клиента обязательно',
        'phone.required' => 'Телефон обязателен',
        'service_id.required' => 'Выберите услугу',
        'source.required' => 'Укажите источник заявки',
        'status.required' => 'Укажите статус',
        'master_id.required' => 'Укажите мастера'
    ];

    public function create()
    {
        $this->resetInputFields();
        $this->status = 'new';
        $this->date = now()->format('Y-m-d');
        $this->time = '09:00'; 
        $this->isOpen = true;
    }

    protected function isValidTimeSlot($time)
    {
        if (!$time || !is_string($time)) {
            return false;
        }
        
       
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            return false;
        }
        
        
        $minutes = explode(':', $time)[1];
        return $minutes === '00' || $minutes === '30';
    }



    public function store()
{
    if (!in_array(auth()->user()->role, ['admin', 'master'])) {
        abort(403);
    }

    $this->validate();

    if (!$this->isValidTimeSlot($this->time)) {
        $this->addError('time', 'Время должно быть в формате xx:00 или xx:30');
        return;
    }

    $service = Service::find($this->service_id);
    $deposit = $service->price / 2; 

    
    $application = Application::create([
        'date' => $this->date,
        'time' => $this->time,
        'client_name' => $this->client_name,
        'phone' => $this->phone,
        'email' => $this->email,
        'service_id' => $this->service_id,
        'source' => 'site',
        'status' => $this->status,
        'master_id' => $this->master_id,
        'notes' => $this->notes,
        'user_id' => auth()->id(),
        'deposit' => $deposit, 
        'payment_status' => 'unpaid',
    ]);

    $timeForDb = $this->time . ':00';
    Schedule::where('master_id', $this->master_id)
        ->where('date', $this->date)
        ->where('time_start', $timeForDb)
        ->update(['status' => 'busy']);

    $this->closeModal();
    session()->flash('success', 'Заявка успешно создана');
    
    $this->dispatch('schedule-updated');
}

    public function getListeners()
{
    return [
        'schedule-updated' => '$refresh',
    ];
}

    public function edit($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        $application = Application::findOrFail($id);
        $this->application_id = $id;
        $this->date = $application->date; 
        $this->time = $application->time; 
        $this->client_name = $application->client_name;
        $this->phone = $application->phone;
        $this->email = $application->email;
        $this->service_id = $application->service_id;
        $this->source = $application->source;
        $this->status = $application->status;
        $this->master_id = $application->master_id;
        $this->notes = $application->notes;
        $this->isOpen = true;
    }

    public function update()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        $this->validate();

        
        if (!$this->isValidTimeSlot($this->time)) {
            $this->addError('time', 'Время должно быть в формате xx:00 или xx:30');
            return;
        }

        $application = Application::findOrFail($this->application_id);
        $application->update([
            'date' => $this->date,
            'time' => $this->time, 
            'client_name' => $this->client_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'service_id' => $this->service_id,
            'source' => $this->source,
            'status' => $this->status,
            'master_id' => $this->master_id,
            'deposit_amount' => $this->depositAmount,
            'notes' => $this->notes
        ]);

        $this->closeModal();
        session()->flash('success', 'Заявка успешно обновлена');
    }

    public function closeModal()
    {
        $this->resetInputFields();
        $this->isOpen = false;
        $this->resetErrorBag();
    }

    private function resetInputFields()
    {
        $this->application_id = null;
        $this->date = '';
        $this->time = '';
        $this->client_name = '';
        $this->phone = '';
        $this->email = '';
        $this->service_id = '';
        $this->source = '';
        $this->status = 'new';
        $this->master_id = '';
        $this->notes = '';
    }

    public function delete($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        Application::findOrFail($id)->delete();
        session()->flash('success', 'Заявка успешно удалена');
        $this->resetPage();
    }

    public function changeStatus($id, $status)
    {
        $application = Application::findOrFail($id);
        $application->update(['status' => $status]);

        $this->closeModal();
        $this->resetPage();

        session()->flash('success', 'Статус заявки обновлен');
    }

    public function updatedServiceId($value)
{
    $service = Service::find($value);
    if ($service) {
        $this->depositAmount = $service->price / 2;
    }
}


}