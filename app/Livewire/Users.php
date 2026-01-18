<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;
    
    public $name, $email, $password, $user_id; 
    public $isOpen = false;
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $filterStatus = 'all';
    public $role = 'client';
    public $filterRole = 'all';

    public function render()
    {
        $query = User::query();

        if (auth()->user()->role === 'master') {
            $query->whereIn('role', ['master', 'client']);
        }

        
        if ($this->filterRole !== 'all') {
            $query->where('role', $this->filterRole);
        }

    $users = $query
        ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate(10);

    

    return view('livewire.users', compact('users'));
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
    protected $messages=[
        'name.reqred'=>'Поле имени обязательно',
        'email.reqred'=>'Поле почты обязательно',
        'email.email'=>'Введите правильный формат почты',
        'email.unique'=>'Почта должна быть уникальной',
    ];

    public function create()
    {
        $this->resetInputFields();
        $this->isOpen = true;
    }

   public function store()
{
    $validatedData = $this->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required',
        'role' => 'required|in:admin,master,client',
    ]);
    
    
    $role = (auth()->user()->role === 'admin') ? $this->role : 'client';

    User::create([
        'name' => $this->name,
        'email' => $this->email,
        'password' => bcrypt($this->password),
        'role' => $role, 
    ]);

    $this->closeModal();
    $this->resetPage();
}

    public function update()
{
    $validated = $this->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email,' . $this->user_id,
        'password' => 'nullable|min:6',
        'role' => 'required|in:admin,master,client',
    ]);

    $user = User::findOrFail($this->user_id);

   
    $user->name = $validated['name'];
    $user->email = $validated['email'];

  
    if (!empty($validated['password'])) {
        $user->password = bcrypt($validated['password']);
    }

   
    if (auth()->user()->role === 'admin') {
        $user->role = $validated['role'];
    }

    $user->save();

    $this->closeModal();
}

    

    public function closeModal()
    {
        $this->resetInputFields();
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->user_id = null;
    }

    public function delete_all()
    {
        $models = User::all();
        foreach ($models as $model) {
            $model->delete();
        }
    }

    public function delete($id)
{
    
    if (auth()->user()->role === 'master') {
        session()->flash('error', 'У вас нет прав на удаление пользователей.');
        return;
    }

    $user = User::findOrFail($id);

    
    if (auth()->user()->id === $user->id) {
        session()->flash('error', 'Вы не можете удалить себя.');
        return;
    }

    $user->delete();

    session()->flash('success', 'Пользователь удалён.');
}

    public function restoreUser($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();
            session()->flash('message', 'User Restored Successfully.'); 
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'User not found.'); 
        }
    }

    public function edit($id)
{
    $user = User::findOrFail($id);
    $this->user_id = $user->id;
    $this->name = $user->name;
    $this->email = $user->email;
    $this->role = $user->role;
    $this->isOpen = true;
}
}
