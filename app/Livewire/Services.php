<?php

namespace App\Livewire;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class Services extends Component
{
    use WithPagination;
    
    public $name, $type, $price, $duration, $description, $service_id; 
    public $isOpen = false;
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $filterType = 'all'; 

    public function render()
    {
        return view('livewire.services', [
            'services' => Service::query()
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                          ->orWhere('description', 'like', '%'.$this->search.'%');
                })
                ->when($this->filterType !== 'all', function ($query) {
                    $query->where('type', $this->filterType);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10)
        ]);
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
        'name' => 'required|min:3',
        'type' => 'required|in:Татуировка,Пирсинг,Лазерное удаление',
        'price' => 'required|numeric|min:500',
        'duration' => 'required',
        'description' => 'required|min:10',
    ];

    protected $messages = [
        'name.required' => 'Название услуги обязательно',
        'type.required' => 'Тип услуги обязателен',
        'price.required' => 'Цена обязательна',
        'price.numeric' => 'Цена должна быть числом',
        'price.min' => 'Цена не может быть меньше 500',
        'duration.required' => 'Длительность обязательна',
        'description.required' => 'Описание обязательно',
        'description.min' => 'Описание должно быть не менее 10 символов',
    ];

    public function create()
    {
        $this->resetInputFields();
        $this->isOpen = true;
    }

    public function store()
{
    if (auth()->user()->role === 'client') {
        abort(403);
    }

    $this->validate();

    Service::create([
        'name' => $this->name,
        'type' => $this->type,
        'price' => $this->price,
        'duration' => $this->duration,
        'description' => $this->description,
    ]);

    $this->closeModal();
    $this->resetPage();
}

    public function edit($id)
{
    if (auth()->user()->role === 'client') {
        abort(403);
    }

    $service = Service::findOrFail($id);
    $this->service_id = $id;
    $this->name = $service->name;
    $this->type = $service->type;
    $this->price = $service->price;
    $this->duration = $service->duration;
    $this->description = $service->description;
    $this->isOpen = true;
}

    public function update()
{
    if (auth()->user()->role === 'client') {
        abort(403);
    }

    $this->validate();

    $service = Service::findOrFail($this->service_id);
    $service->update([
        'name' => $this->name,
        'type' => $this->type,
        'price' => $this->price,
        'duration' => $this->duration,
        'description' => $this->description,
    ]);

    $this->closeModal();
}

    public function closeModal()
    {
        $this->resetInputFields();
        $this->isOpen = false;
        $this->resetErrorBag();
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->type = '';
        $this->price = '';
        $this->duration = '';
        $this->description = '';
        $this->service_id = null;
    }

    public function delete($id)
{
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }

    Service::findOrFail($id)->delete();
    session()->flash('message', 'Услуга успешно удалена'); 
    $this->resetPage();
}
}