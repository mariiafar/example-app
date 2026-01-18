<div class="max-w-6xl mx-auto p-4 bg-white text-gray-900 dark:bg-gray-900 dark:text-white shadow-lg rounded-lg">
    
    <h1 class="text-xl font-bold mb-6 text-center">Мои записи</h1>

    {{-- Панель поиска и фильтров --}}
    <div class="mb-4 space-y-3">
        {{-- Поиск --}}
        <div>
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Поиск по услуге или мастеру..." 
                class="w-full p-2 border rounded text-sm text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
        </div>

        {{-- Фильтры и сортировка --}}
        <div class="flex flex-col sm:flex-row gap-2">
            {{-- Фильтр по статусу --}}
            <select 
                wire:model.live="filterStatus" 
                class="p-2 border rounded text-sm text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
                <option value="all">Все статусы</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            {{-- Сортировка --}}
            <select 
                wire:model.live="sortField" 
                class="p-2 border rounded text-sm text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
                <option value="date">По дате</option>
                <option value="created_at">По созданию</option>
                <option value="status">По статусу</option>
            </select>
            
            <select 
                wire:model.live="sortDirection" 
                class="p-2 border rounded text-sm text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
                <option value="desc">Сначала новые</option>
                <option value="asc">Сначала старые</option>
            </select>
        </div>
    </div>

    {{-- Модальное окно для просмотра деталей --}}
    @if($isOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50 p-4">
            <div class="max-w-md w-full mx-auto p-6 bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Детали записи</h2>
                    <button wire:click="closeModal" 
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        ✕
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Дата</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $date ? \Carbon\Carbon::parse($date)->format('d.m.Y') : '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Время</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($time)
                                    @if(strlen($time) === 8)
                                        {{ substr($time, 0, 5) }}
                                    @else
                                        {{ $time }}
                                    @endif
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Услуга</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $services->firstWhere('id', $service_id)?->name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Мастер</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $masters->firstWhere('id', $master_id)?->name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Статус</label>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $status === 'new' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $status === 'canceled' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $statuses[$status] ?? $status }}
                            </span>
                        </p>
                    </div>

                    @if($notes)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Заметки</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $notes }}</p>
                        </div>
                    @endif

                    @if($deposit)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Депозит</label>
                            <p class="mt-1 text-sm text-green-600 font-semibold">{{ number_format($deposit, 2) }} ₽</p>
                        </div>
                    @endif

                    <div class="pt-4 border-t dark:border-gray-700">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Источник</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $sources[$source] ?? $source }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Статус оплаты</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment_status ?? 'Не указан' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button wire:click="closeModal" 
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 rounded text-sm">
                        Закрыть
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Таблица записей --}}
    <div class="overflow-x-auto text-sm">
        @if($applications->count() > 0)
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-xs">
                        <th class="border p-3">Дата</th>
                        <th class="border p-3">Время</th>
                        <th class="border p-3">Услуга</th>
                        <th class="border p-3">Мастер</th>
                        <th class="border p-3">Статус</th>
                        <th class="border p-3">Сумма</th>
                        <th class="border p-3">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $application)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 text-xs">
                            <td class="border p-3 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($application->date)->format('d.m.Y') }}
                            </td>
                            <td class="border p-3">
                                @php
                                    $displayTime = $application->time;
                                    if (is_string($displayTime) && strlen($displayTime) === 8) {
                                        $displayTime = substr($displayTime, 0, 5);
                                    }
                                @endphp
                                {{ $displayTime ?: '-' }}
                            </td>
                            <td class="border p-3">
                                {{ $application->service->name ?? '-' }}
                                @if($application->notes)
                                    <br>
                                    <small class="text-gray-500 text-xs">{{ Str::limit($application->notes, 30) }}</small>
                                @endif
                            </td>
                            <td class="border p-3">{{ $application->master?->name ?? '—' }}</td>
                            <td class="border p-3">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $application->status === 'new' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $application->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $application->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $application->status === 'canceled' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $statuses[$application->status] ?? $application->status }}
                                </span>
                            </td>
                            <td class="border p-3">
                                @if($application->deposit)
                                    <span class="font-semibold text-green-600">
                                        {{ number_format($application->deposit, 2) }} ₽
                                    </span>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="border p-3" >
                                <button wire:click="view({{ $application->id }})" style="color: black;"
                                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs">
                                    Просмотр
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center py-8">
                <div class="mb-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Записей не найдено</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">
                    @if($search || $filterStatus !== 'all')
                        Попробуйте изменить параметры поиска
                    @else
                        У вас пока нет записей на услуги
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- Пагинация --}}
    @if($applications->hasPages())
        <div class="mt-6">
            {{ $applications->links('pagination.schedules') }}
        </div>
    @endif
</div>