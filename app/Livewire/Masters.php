<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class Masters extends Component
{
    use WithPagination;
    
    public $selectedMaster = null;
    public $availableServices = [];
    public $masterServices = [];
    public $isEditModalOpen = false;
    public $search = '';

    public function render()
    {
        $masters = User::where('role', 'master')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->with('services')
            ->paginate(10);

        return view('livewire.masters', [
            'masters' => $masters,
        ]);
    }

    public function openEditModal($masterId)
    {
        $this->selectedMaster = User::with('services')->find($masterId);
        
        if (!$this->selectedMaster) {
            session()->flash('error', 'Мастер не найден');
            return;
        }

        // Получаем все доступные услуги
        $this->availableServices = Service::all();
        
        // Получаем ID услуг, которые уже есть у мастера
        $this->masterServices = $this->selectedMaster->services->pluck('id')->toArray();
        
        $this->isEditModalOpen = true;
    }

    public function closeEditModal()
    {
        $this->selectedMaster = null;
        $this->availableServices = [];
        $this->masterServices = [];
        $this->isEditModalOpen = false;
    }

    public function toggleService($serviceId)
    {
        if (in_array($serviceId, $this->masterServices)) {
            // Удаляем услугу
            $this->masterServices = array_values(array_diff($this->masterServices, [$serviceId]));
        } else {
            // Добавляем услугу
            $this->masterServices[] = $serviceId;
        }
    }

    public function saveMasterServices()
    {
        if (!$this->selectedMaster) {
            session()->flash('error', 'Мастер не выбран');
            return;
        }

        // Синхронизируем услуги мастера
        $this->selectedMaster->services()->sync($this->masterServices);
        
        session()->flash('success', 'Услуги мастера успешно обновлены');
        
        $this->closeEditModal();
        
        // Обновляем данные
        $this->resetPage();
    }
}
