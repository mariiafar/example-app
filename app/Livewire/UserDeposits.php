<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\PaymentTransaction;
use Bavix\Wallet\Models\Wallet;

class UserDeposits extends Component
{
    use WithPagination;

    public $userId;
    public $depositBalance = 0;
    public $totalDeposited = 0;
    public $totalRefunded = 0;
    public $totalTransferred = 0;
    public $search = '';
    public $filterType = 'all';

    public function mount($userId = null)
    {
        $this->userId = $userId ?? auth()->id();
        
        
        if ($userId && $userId != auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'У вас нет прав для просмотра чужих депозитов');
        }
    }

    public function render()
{
    $user = User::findOrFail($this->userId);
    
   
    $user->ensureDepositWallet();
    
   
    $wallet = $user->getDepositWallet();
    $this->depositBalance = $wallet->balance;

   
    $query = PaymentTransaction::where('user_id', $this->userId)
        ->whereIn('type', [
            PaymentTransaction::TYPE_DEPOSIT,
            PaymentTransaction::TYPE_REFUND,
            PaymentTransaction::TYPE_TRANSFER_TO_MASTER
        ])
        ->with('application');

    
    if ($this->search) {
        $query->where(function($q) {
            $q->where('description', 'like', '%' . $this->search . '%')
              ->orWhere('payment_id', 'like', '%' . $this->search . '%')
              ->orWhereHas('application', function($q2) {
                  $q2->where('id', 'like', '%' . $this->search . '%');
              });
        });
    }

    
    if ($this->filterType !== 'all') {
        $query->where('type', $this->filterType);
    }

    $transactions = $query->latest()->paginate(10);

    
    $this->totalDeposited = PaymentTransaction::where('user_id', $this->userId)
        ->where('type', PaymentTransaction::TYPE_DEPOSIT)
        ->where('status', 'completed')
        ->sum('amount');

    $this->totalRefunded = PaymentTransaction::where('user_id', $this->userId)
        ->where('type', PaymentTransaction::TYPE_REFUND)
        ->where('status', 'completed')
        ->sum('amount');

    $this->totalTransferred = PaymentTransaction::where('user_id', $this->userId)
        ->where('type', PaymentTransaction::TYPE_TRANSFER_TO_MASTER)
        ->where('status', 'completed')
        ->sum('amount');

    return view('livewire.user-deposits', [
        'user' => $user,
        'wallet' => $wallet,
        'transactions' => $transactions,
    ]);
}

   
    public function createDepositWallet()
    {
        $user = User::findOrFail($this->userId);
        
        if (!$user->hasWallet('deposit')) {
            $user->createWallet([
                'name' => 'Депозитный счет',
                'slug' => 'deposit',
                'meta' => ['type' => 'deposit', 'created_at' => now()],
            ]);
            
            session()->flash('success', '✅ Депозитный кошелек успешно создан!');
            $this->depositBalance = 0;
        } else {
            session()->flash('info', 'ℹ️ Депозитный кошелек уже существует');
        }
    }

   
    public function addTestDeposit()
    {
        $user = User::findOrFail($this->userId);
        
        if (!$user->hasWallet('deposit')) {
            $this->createDepositWallet();
        }
        
        $wallet = $user->getWallet('deposit');
        $wallet->deposit(1000, [
            'description' => 'Тестовое пополнение депозита',
            'type' => 'test_deposit',
        ]);
        
        $this->depositBalance = $wallet->balance;
        session()->flash('success', '✅ Добавлено 1000 ₽ на депозитный счет!');
    }

    
    public function resetFilters()
    {
        $this->reset(['search', 'filterType']);
        $this->resetPage();
    }
}