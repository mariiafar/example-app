<div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">💳 Мои депозиты</h1>
        <a href="{{ route('schedule-browser') }}" 
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition duration-200">
            ← Назад к расписанию
        </a>
    </div>

    <!-- Уведомления -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('info'))
        <div class="mb-6 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
            {{ session('info') }}
        </div>
    @endif

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Текущий баланс</h3>
            <p class="text-2xl font-bold text-green-600">{{ number_format($depositBalance, 2) }} ₽</p>
            @if(!$depositBalance)
                <button wire:click="createDepositWallet" 
                        class="mt-2 text-sm text-green-600 hover:text-green-800 underline">
                    Создать депозитный кошелек
                </button>
            @endif
        </div>

        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Всего внесено</h3>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($totalDeposited, 2) }} ₽</p>
        </div>

        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Возвращено</h3>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($totalRefunded, 2) }} ₽</p>
        </div>

        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Использовано</h3>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($totalTransferred, 2) }} ₽</p>
        </div>
    </div>

    <!-- Панель управления (для тестирования) -->
    @if(auth()->user()->role === 'admin' || auth()->user()->email === 'test@example.com')
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h4 class="font-medium text-yellow-800 mb-2">⚙️ Панель тестирования (только для разработки)</h4>
            <div class="flex gap-2">
                <button wire:click="createDepositWallet" 
                        class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded">
                    Создать кошелек
                </button>
                <button wire:click="addTestDeposit" 
                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded">
                    +1000 ₽ тест
                </button>
                <button wire:click="resetFilters" 
                        class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded">
                    Сбросить фильтры
                </button>
            </div>
        </div>
    @endif

    <!-- Панель поиска и фильтров -->
    <div class="mb-6 bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                <input type="text" wire:model.live="search" 
                       placeholder="Поиск по описанию или ID..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Тип операции</label>
                <select wire:model.live="filterType" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">Все типы</option>
                    <option value="deposit">Депозиты</option>
                    <option value="refund">Возвраты</option>
                    <option value="transfer_to_master">Переводы мастеру</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button wire:click="resetFilters" 
                        class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium">
                    Сбросить фильтры
                </button>
            </div>
        </div>
    </div>

    <!-- История транзакций -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                📋 История операций
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Дата
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Тип операции
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Заявка
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Сумма
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Описание
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transaction->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full {{ $transaction->typeColor }}">
                                    {{ $transaction->typeLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($transaction->application)
                                    <a href="{{ route('applications') }}?search={{ $transaction->application_id }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        #{{ $transaction->application_id }}
                                    </a>
                                    @if($transaction->application->service)
                                        <div class="text-xs text-gray-500">
                                            {{ $transaction->application->service->name }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                <span class="{{ in_array($transaction->type, ['deposit', 'refund']) ? 'text-green-600' : 'text-red-600' }}">
                                    {{ in_array($transaction->type, ['deposit', 'refund']) ? '+' : '-' }}
                                    {{ number_format($transaction->amount, 2) }} ₽
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $transaction->description }}
                                @if($transaction->status !== 'completed')
                                    <br>
                                    <span class="text-xs px-1 py-0.5 rounded {{ $transaction->statusColor }}">
                                        {{ $transaction->statusLabel }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">
                                <div class="text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-lg">Нет операций по депозитам</p>
                                    <p class="text-sm mt-1">Здесь будет отображаться история всех депозитных операций</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        @if($transactions->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Информация о системе депозитов -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
            <h4 class="text-lg font-medium text-blue-800 mb-3">💰 Как работает система депозитов?</h4>
            <ul class="space-y-2 text-blue-700">
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>При записи на услугу оплачивается депозит (50% от стоимости)</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Депозит резервируется на вашем счете</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>После выполнения услуги депозит переводится мастеру</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>При отмене записи депозит возвращается на ваш счет</span>
                </li>
            </ul>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-5">
            <h4 class="text-lg font-medium text-green-800 mb-3">✅ Преимущества системы</h4>
            <ul class="space-y-2 text-green-700">
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Быстрая оплата без ввода данных карты каждый раз</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Полная прозрачность всех операций</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Автоматический возврат при отмене</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Возможность использовать баланс для новых записей</span>
                </li>
            </ul>
        </div>
    </div>
</div>