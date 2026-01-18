<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Application;
use App\Models\Service;
use App\Models\User;

class ClientApplications extends Component
{
    use WithPagination;

    public $isOpen = false;
    public $application_id;
    public $date;
    public $time;
    public $client_name;
    public $phone;
    public $email;
    public $service_id;
    public $master_id;
    public $notes;
    public $status;
    public $source;
    public $deposit;
    public $payment_status;
    
    public $search = '';
    public $filterStatus = 'all';
    public $sortField = 'date';
    public $sortDirection = 'desc';
    
    // Только для чтения - клиент не может редактировать заявки
    public $readOnly = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => 'all'],
        'sortField' => ['except' => 'date'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // Массивы для выпадающих списков
    public $sources = [
        'website' => 'Сайт',
        'phone' => 'Телефон',
        'instagram' => 'Instagram',
        'recommendation' => 'Рекомендация',
        'other' => 'Другое',
    ];

    public $statuses = [
        'new' => 'Новая',
        'confirmed' => 'Подтверждена',
        'completed' => 'Выполнена',
        'canceled' => 'Отменена',
    ];

    public function mount()
    {
        // Устанавливаем данные текущего пользователя
        $user = auth()->user();
        $this->client_name = $user->name;
        $this->phone = $user->phone;
        $this->email = $user->email;
    }

    public function render()
    {
        $user = auth()->user();
        
        // Запрос для получения заявок текущего пользователя
        $query = Application::with(['service', 'master'])
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->orWhere('phone', $user->phone);
        
        // Поиск
        if ($this->search) {
            $query->where(function($q) {
                $q->where('client_name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Фильтр по статусу
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        // Сортировка
        $query->orderBy($this->sortField, $this->sortDirection);

        $applications = $query->paginate(15);

        // Получаем услуги и мастеров для формы (если понадобится)
        $services = Service::all();
        $masters = User::where('role', 'master')->get();

        return view('livewire.client-applications', [
            'applications' => $applications,
            'services' => $services,
            'masters' => $masters,
        ]);
    }

    // Просмотр деталей заявки (только просмотр)
    public function view($id)
    {
        $application = Application::findOrFail($id);
        
        // Проверяем, что заявка принадлежит текущему пользователю
        $user = auth()->user();
        if ($application->user_id != $user->id && 
            $application->email != $user->email && 
            $application->phone != $user->phone) {
            session()->flash('error', 'У вас нет доступа к этой заявке');
            return;
        }

        $this->application_id = $id;
        $this->date = $application->date;
        $this->time = $application->time;
        $this->client_name = $application->client_name;
        $this->phone = $application->phone;
        $this->email = $application->email;
        $this->service_id = $application->service_id;
        $this->master_id = $application->master_id;
        $this->notes = $application->notes;
        $this->status = $application->status;
        $this->source = $application->source;
        $this->deposit = $application->deposit;
        $this->payment_status = $application->payment_status;
        
        $this->isOpen = true;
    }

    // Закрыть модальное окно
    public function closeModal()
    {
        $this->resetValidation();
        $this->reset([
            'application_id', 'date', 'time', 'client_name', 'phone', 'email',
            'service_id', 'master_id', 'notes', 'status', 'source', 'deposit',
            'payment_status', 'isOpen'
        ]);
        
        // Восстанавливаем данные пользователя
        $user = auth()->user();
        $this->client_name = $user->name;
        $this->phone = $user->phone;
        $this->email = $user->email;
    }
}