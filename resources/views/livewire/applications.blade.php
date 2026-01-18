<div class="max-w-6xl mx-auto p-4 bg-white text-gray-900 dark:bg-gray-900 dark:text-white shadow-lg rounded-lg">
    

    {{-- Кнопка создания --}}
    @if(in_array(auth()->user()->role, ['admin', 'master']))
    <div class="mb-4">
        <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-gray-900 dark:text-white px-4 py-2 rounded mb-4">
            + Добавить заявку
        </button>
    </div>
    @endif

    {{-- Панель поиска и фильтров --}}
    <div class="mb-4 space-y-3">
        {{-- Поиск --}}
        <div>
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Поиск по клиенту, телефону или email..." 
                class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
        </div>

        {{-- Фильтры и сортировка --}}
        <div class="flex flex-col sm:flex-row gap-2">
            {{-- Фильтр по статусу --}}
            <select 
                wire:model.live="filterStatus" 
                class="p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
                <option value="all">Все статусы</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            {{-- Сортировка --}}
            <select 
                wire:model.live="sortField" 
                class="p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
                <option value="date">По дате</option>
                <option value="created_at">По созданию</option>
                <option value="client_name">По имени</option>
            </select>
            
            <select 
                wire:model.live="sortDirection" 
                class="p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
                <option value="asc">↑ Возр.</option>
                <option value="desc">↓ Убыв.</option>
            </select>
        </div>
    </div>

    {{-- Форма создания/редактирования --}}
    @if($isOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50">
            <div class="max-w-2xl w-full mx-4 p-4 bg-white dark:bg-gray-800 shadow-lg rounded-lg">
                <h2 class="text-lg font-semibold mb-3">{{ $application_id ? 'Редактировать заявку' : 'Создать заявку' }}</h2>
                
                <form wire:submit.prevent="{{ $application_id ? 'update' : 'store' }}">
                    <div class="space-y-3">

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block mb-1 text-sm">Дата</label>
                                <input type="date" wire:model="date" 
                                       class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-700 dark:border-gray-600">
                                @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block mb-1 text-sm">Время</label>
                                <select wire:model="time" class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-700 dark:border-gray-600">
                                    <option value="">Выберите время</option>
                                    @foreach($timeSlots as $slot)
                                        <option value="{{ $slot }}" {{ $time === $slot ? 'selected' : '' }}>{{ $slot }}</option>
                                    @endforeach
                                </select>
                                @error('time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm">Имя клиента</label>
                            <input type="text" wire:model="client_name" placeholder="Имя клиента" 
                                   class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-700 dark:border-gray-600">
                            @error('client_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block mb-1 text-sm">Телефон</label>
                                <input type="text" wire:model="phone" placeholder="Телефон" 
                                       class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-700 dark:border-gray-600">
                                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block mb-1 text-sm">Email</label>
                                <input type="email" wire:model="email" placeholder="Email" 
                                       class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-700 dark:border-gray-600">
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block mb-1 text-sm">Услуга</label>
                                <select wire:model="service_id" 
                                        class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-700 dark:border-gray-600">
                                    <option value="">Выберите услугу</option>
                                    @if($depositAmount)
    <div class="mt-2 text-green-600 font-semibold">
        Депозит: {{ $depositAmount }} ₽
    </div>
@endif
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ $service_id == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                                    @endforeach
                                </select>
                                @error('service_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block mb-1 text-sm">Мастер</label>
                                <select wire:model="master_id" class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-700 dark:border-gray-600">
                                    <option value="">Выберите мастера</option>
                                    @foreach(\App\Models\User::where('role', 'master')->get() as $master)
                                        <option value="{{ $master->id }}" {{ $master_id == $master->id ? 'selected' : '' }}>{{ $master->name }}</option>
                                    @endforeach
                                </select>
                                @error('master_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block mb-1 text-sm">Источник</label>
                                <select wire:model="source" 
                                        class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-700 dark:border-gray-600">
                                    <option value="">Выберите источник</option>
                                    @foreach($sources as $key => $label)
                                        <option value="{{ $key }}" {{ $source === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('source') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block mb-1 text-sm">Статус</label>
                                <select wire:model="status" 
                                        class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-700 dark:border-gray-600">
                                    <option value="">Выберите статус</option>
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm">Заметки</label>
                            <textarea wire:model="notes" rows="2" 
                                      class="w-full p-1.5 border rounded text-sm text-gray-900 dark:bg-gray-700 dark:border-gray-600">{{ $notes }}</textarea>
                            @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                    </div>
                    
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" wire:click="closeModal" 
                                class="px-3 py-1 bg-gray-300 hover:bg-gray-400 rounded text-sm">
                            Отмена
                        </button>
                        <button type="submit" 
                                style="background-color: #3b82f6; color: white;" class="px-3 py-1 rounded text-sm">
                            {{ $application_id ? 'Обновить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Таблица заявок --}}
    <div class="overflow-x-auto text-sm">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-xs">
                    <th class="border p-2">Дата</th>
                    <th class="border p-2">Время</th>
                    <th class="border p-2">Клиент</th>
                    <th class="border p-2">Телефон</th>
                    <th class="border p-2">Email</th>
                    <th class="border p-2">Услуга</th>
                    <th class="border p-2">Источник</th>
                    <th class="border p-2">Мастер</th>
                    <th class="border p-2">Статус</th>
                    @if(auth()->user()->role === 'admin')
                        <th class="border p-2">Действия</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 text-xs">
                        <td class="border p-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($application->date)->format('d.m.Y') }}</td>
                        <td class="border p-2">
                            @php
                                $displayTime = $application->time;
                                if (is_string($displayTime) && strlen($displayTime) === 8) {
                                    $displayTime = substr($displayTime, 0, 5);
                                }
                            @endphp
                            {{ $displayTime ?: '-' }}
                        </td>
                        <td class="border p-2">{{ $application->client_name }}</td>
                        <td class="border p-2 whitespace-nowrap">{{ $application->phone }}</td>
                        <td class="border p-2">{{ $application->email ?: '—' }}</td>
                        <td class="border p-2">{{ $application->service->name ?? '-' }}</td>
                        <td class="border p-2">{{ $sources[$application->source] ?? $application->source }}</td>
                        <td class="border p-2">{{ $application->master?->name ?? '—' }}</td>
                        <td class="border p-2">
                            <span class="px-1.5 py-0.5 text-xs rounded-full 
                                {{ $application->status === 'new' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $application->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $application->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $application->status === 'canceled' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $statuses[$application->status] ?? $application->status }}
                            </span>
                        </td>
                        @if(auth()->user()->role === 'admin')
                            <td class="border p-2 space-x-1 whitespace-nowrap">
                                <button wire:click="edit({{ $application->id }})" 
                                        style="background-color: #3b82f6; color: white;" class="px-2 py-0.5 rounded text-xs">
                                    Ред.
                                </button>
                                <button wire:click="delete({{ $application->id }})" 
                                        onclick="return confirm('Удалить эту заявку?')"
                                        class="px-2 py-0.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs">
                                    Уд.
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'admin' ? 10 : 9 }}" class="border p-3 text-center text-gray-500 text-sm">Нет заявок</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

   
   <div class="mt-4">
        {{ $applications->links('pagination.schedules') }}
    </div>
</div>