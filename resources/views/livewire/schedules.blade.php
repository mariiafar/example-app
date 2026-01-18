<div class="max-w-6xl mx-auto p-4 bg-white shadow-lg rounded-lg">
    
    {{-- Панель фильтрации --}}
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-4 gap-2">
        {{-- Поиск по мастеру (только для админа) --}}
        @if(auth()->user()->role === 'admin')
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Поиск по мастеру..." 
                class="w-full p-1.5 border border-gray-300 rounded text-sm shadow-sm"
            >
        @endif

        {{-- Фильтр по статусу --}}
        <select wire:model.live="filterStatus" class="w-full p-1.5 border rounded text-sm shadow-sm">
            <option value="all">Все статусы</option>
            <option value="available">Свободно</option>
            <option value="booked">Забронировано</option>
            <option value="busy">Занято</option>
            <option value="canceled">Отменено</option>
        </select>

        {{-- Сортировка по полю --}}
        <select wire:model.live="sortField" class="w-full p-1.5 border rounded text-sm shadow-sm">
            <option value="date">По дате</option>
            <option value="time_start">По времени</option>
            <option value="updated_at">По последнему изменению</option>
        </select>

        {{-- Направление сортировки --}}
        <select wire:model.live="sortDirection" class="w-full p-1.5 border rounded text-sm shadow-sm">
            <option value="asc">↑ Возр.</option>
            <option value="desc">↓ Убыв.</option>
        </select>
    </div>

    {{-- Кнопка добавления (только для мастеров и админов) --}}
    @if(in_array(auth()->user()->role, ['admin', 'master']))
        <div class="mb-3 text-left">
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-gray-900 dark:text-white px-4 py-2 rounded mb-4">
                + Добавить запись
            </button>
        </div>
    @endif

    {{-- Таблица расписания --}}
    <div class="overflow-x-auto text-sm">
        <table class="w-full border border-collapse">
            <thead class="bg-gray-100 dark:bg-gray-700 text-xs">
                <tr>
                    @if(auth()->user()->role === 'admin')
                        <th class="border p-2">Мастер</th>
                    @endif
                    <th class="border p-2">Дата</th>
                    <th class="border p-2">Время</th>
                    <th class="border p-2">Статус</th>
                    <th class="border p-2">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schedules as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 text-xs">
                        @if(auth()->user()->role === 'admin')
                            <td class="border p-2">{{ $item->master?->name ?? '—' }}</td>
                        @endif
                        <td class="border p-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($item->date)->format('d.m.Y') }}</td>
                        <td class="border p-2">{{ $item->time_start }} — {{ $item->time_end }}</td>
                        <td class="border p-2">
                            @php
                                $statusLabels = [
                                    'available' => ['Свободно', 'text-green-800 bg-green-200'],
                                    'booked' => ['Забронировано', 'text-purple-800 bg-purple-200'],
                                    'busy' => ['Занято', 'text-red-800 bg-red-200'],
                                    'canceled' => ['Отменено', 'text-gray-800 bg-gray-200'],
                                ];
                                $statusClass = $statusLabels[$item->status][1] ?? '';
                                $statusText = $statusLabels[$item->status][0] ?? $item->status;
                            @endphp
                            <span class="inline-block px-1.5 py-0.5 text-xs rounded-full {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="border p-2 space-x-1 whitespace-nowrap">
                            @if(auth()->user()->role === 'admin' || auth()->user()->name === $item->master?->name)
                                <button wire:click="edit({{ $item->id }})"
                                        style="background-color: #3b82f6; color: white;" class="px-2 py-0.5 rounded text-xs">
                                    Ред.
                                </button>
                                <button wire:click="delete({{ $item->id }})"
                                        onclick="return confirm('Удалить эту запись?')"
                                        class="px-2 py-0.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs">
                                    Уд.
                                </button>
                            @else
                                <span class="text-gray-400 italic text-xs">Нет доступа</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'admin' ? '5' : '4' }}" class="border p-3 text-center text-gray-500 text-sm">Нет записей в расписании</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Модальное окно --}}
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-xl shadow-lg p-4 w-full max-w-lg">
            <h2 class="text-lg font-bold mb-3">
                {{ $schedule_id ? 'Редактировать запись' : 'Добавить запись' }}
            </h2>

            @if($schedule_id)
                {{-- Редактирование существующей записи --}}
                <form wire:submit.prevent="update">
                    {{-- Мастер --}}
                    <div class="mb-3">
                        <label class="block mb-1 text-xs font-medium">Мастер</label>
                        <select wire:model="master_id" class="w-full p-1.5 border rounded text-sm">
                            <option value="">Выберите мастера</option>
                            @foreach(\App\Models\User::where('role', 'master')->get() as $master)
                                <option value="{{ $master->id }}">{{ $master->name }}</option>
                            @endforeach
                        </select>
                        @error('master_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Дата --}}
                    <div class="mb-3">
                        <label class="block mb-1 text-xs font-medium">Дата</label>
                        <input type="date" 
                               wire:model="date" 
                               class="w-full p-1.5 border rounded text-sm">
                        @error('date') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Время --}}
                    <div class="mb-3 grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-xs font-medium">Начало</label>
                            <select wire:model="time_start" class="w-full p-1.5 border rounded text-sm">
                                <option value="">Выберите время</option>
                                @foreach($timeSlotsList as $slot)
                                    <option value="{{ $slot }}">{{ $slot }}</option>
                                @endforeach
                            </select>
                            @error('time_start') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium">Конец</label>
                            <select wire:model="time_end" class="w-full p-1.5 border rounded text-sm">
                                <option value="">Выберите время</option>
                                @foreach($timeSlotsList as $slot)
                                    <option value="{{ $slot }}">{{ $slot }}</option>
                                @endforeach
                            </select>
                            @error('time_end') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Статус --}}
                    <div class="mb-3">
                        <label class="block mb-1 text-xs font-medium">Статус</label>
                        <select wire:model="status" class="w-full p-1.5 border rounded text-sm">
                            <option value="available">Свободно</option>
                            <option value="booked">Забронировано</option>
                            <option value="busy">Занято</option>
                            <option value="canceled">Отменено</option>
                        </select>
                        @error('status') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Кнопки --}}
                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" wire:click="closeModal"
                                class="bg-gray-300 px-3 py-1 rounded text-sm hover:bg-gray-400">
                            Отмена
                        </button>
                        <button type="submit"
                                style="background-color: #3b82f6; color: white;" class="px-3 py-1 rounded text-sm">
                            Обновить
                        </button>
                    </div>
                </form>
            @else
                {{-- Создание новой записи с временной линией --}}
                <div>
                    {{-- Шаг 1: Выбор мастера --}}
                    <div class="mb-3">
                        <label class="block mb-1 text-xs font-medium">Шаг 1: Выберите мастера</label>
                        <select wire:model.live="master_id" class="w-full p-1.5 border rounded text-sm">
                            <option value="">Выберите мастера</option>
                            @foreach(\App\Models\User::where('role', 'master')->get() as $master)
                                <option value="{{ $master->id }}">{{ $master->name }}</option>
                            @endforeach
                        </select>
                        @error('master_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Шаг 2: Выбор даты (показывается только после выбора мастера) --}}
                    @if($master_id)
                        <div class="mb-3">
                            <label class="block mb-1 text-xs font-medium">Шаг 2: Выберите дату</label>
                            <input type="date" 
                                   wire:model.live="date" 
                                   class="w-full p-1.5 border rounded text-sm">
                            @error('date') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Шаг 3: Временная линия (показывается только после выбора даты) --}}
                    @if($master_id && $date)
                        <div wire:key="timeline-{{ $master_id }}-{{ $date }}" class="mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg p-4 shadow-inner">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-md font-medium">
                                    Время на {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}
                                </h4>
                                <button
                                    type="button"
                                    wire:click="createWorkingDay"
                                    class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-black rounded text-xs font-medium">
                                    Сделать весь день рабочим
                                </button>
                            </div>

                            @if(count($timeSlots) > 0)
                                @php
                                    $half = ceil(count($timeSlots) / 2);
                                    $row1 = array_slice($timeSlots, 0, $half);
                                    $row2 = array_slice($timeSlots, $half);
                                @endphp

                                <div class="space-y-4">
                                    {{-- Первая строка --}}
                                    <div class="flex gap-3 justify-center flex-wrap">
                                        @foreach ($row1 as $slot)
                                            @php
                                                $hasSlot = $slot['schedule'] !== null;
                                                $isAvailable = $slot['available'] && !$slot['busy'];
                                                $isBusy = $slot['busy'];
                                                $isBookedStatus = $slot['isBookedStatus'] ?? false;
                                                $isCanceled = $slot['isCanceled'] ?? false;
                                                $isMenuOpen = $selectedSlotForMenu === $slot['time'];
                                            @endphp
                                            <div class="relative" wire:key="slot-container-{{ $master_id }}-{{ $date }}-{{ $slot['time'] }}">
                                                <button
                                                    type="button"
                                                    wire:click="openSlotMenu('{{ $slot['time'] }}')"
                                                    class="px-3 py-2 rounded-lg border-2 text-sm font-medium shadow-sm min-w-[70px] text-center transition-all duration-200
                                                        @if($isBookedStatus)
                                                            border-purple-500 bg-purple-100 text-purple-700 hover:bg-purple-200
                                                        @elseif($isCanceled)
                                                            border-gray-400 bg-gray-200 text-gray-600 hover:bg-gray-300
                                                        @elseif($isBusy)
                                                            border-red-500 bg-red-100 text-red-700 hover:bg-red-200
                                                        @elseif($isAvailable)
                                                            border-green-500 bg-green-100 text-green-700 hover:bg-green-200
                                                        @else
                                                            border-gray-300 bg-gray-50 text-gray-500 hover:bg-gray-100
                                                        @endif">
                                                    {{ $slot['time'] }}
                                                    <div class="text-xs mt-1">
                                                        @if($isBookedStatus)
                                                            Забронировано
                                                        @elseif($isCanceled)
                                                            Отменено
                                                        @elseif($isBusy)
                                                            Занято
                                                        @elseif($isAvailable)
                                                            Свободно
                                                        @else
                                                            Не создан
                                                        @endif
                                                    </div>
                                                </button>
                                                
                                                {{-- Выпадающее меню --}}
                                                @if($isMenuOpen)
                                                    <div class="absolute z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg min-w-[150px]"
                                                         style="left: 50%; transform: translateX(-50%);">
                                                        <div class="py-1">
                                                            <button wire:click="setSlotStatus('{{ $slot['time'] }}', 'available')"
                                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-green-50 text-gray-700 flex items-center">
                                                                <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                                                                Свободно
                                                            </button>
                                                            <button wire:click="setSlotStatus('{{ $slot['time'] }}', 'booked')"
                                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-purple-50 text-gray-700 flex items-center">
                                                                <span class="w-3 h-3 rounded-full bg-purple-500 mr-2"></span>
                                                                Забронировано
                                                            </button>
                                                            <button wire:click="setSlotStatus('{{ $slot['time'] }}', 'busy')"
                                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-gray-700 flex items-center">
                                                                <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                                                                Занято
                                                            </button>
                                                            <button wire:click="setSlotStatus('{{ $slot['time'] }}', 'canceled')"
                                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 text-gray-700 flex items-center">
                                                                <span class="w-3 h-3 rounded-full bg-gray-400 mr-2"></span>
                                                                Отменено
                                                            </button>
                                                            @if($hasSlot)
                                                                <div class="border-t border-gray-200 my-1"></div>
                                                                <button wire:click="setSlotStatus('{{ $slot['time'] }}', 'delete')"
                                                                        class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-red-600 flex items-center">
                                                                    <span class="w-3 h-3 rounded-full border-2 border-red-500 mr-2"></span>
                                                                    Удалить слот
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Вторая строка --}}
                                    <div class="flex gap-3 justify-center flex-wrap">
                                        @foreach ($row2 as $slot)
                                            @php
                                                $hasSlot = $slot['schedule'] !== null;
                                                $isAvailable = $slot['available'] && !$slot['busy'];
                                                $isBusy = $slot['busy'];
                                                $isBookedStatus = $slot['isBookedStatus'] ?? false;
                                                $isCanceled = $slot['isCanceled'] ?? false;
                                                $isMenuOpen = $selectedSlotForMenu === $slot['time'];
                                            @endphp
                                            <div class="relative" wire:key="slot-container-{{ $master_id }}-{{ $date }}-{{ $slot['time'] }}">
                                                <button
                                                    type="button"
                                                    wire:click="openSlotMenu('{{ $slot['time'] }}')"
                                                    class="px-3 py-2 rounded-lg border-2 text-sm font-medium shadow-sm min-w-[70px] text-center transition-all duration-200
                                                        @if($isBookedStatus)
                                                            border-purple-500 bg-purple-100 text-purple-700 hover:bg-purple-200
                                                        @elseif($isCanceled)
                                                            border-gray-400 bg-gray-200 text-gray-600 hover:bg-gray-300
                                                        @elseif($isBusy)
                                                            border-red-500 bg-red-100 text-red-700 hover:bg-red-200
                                                        @elseif($isAvailable)
                                                            border-green-500 bg-green-100 text-green-700 hover:bg-green-200
                                                        @else
                                                            border-gray-300 bg-gray-50 text-gray-500 hover:bg-gray-100
                                                        @endif">
                                                    {{ $slot['time'] }}
                                                    <div class="text-xs mt-1">
                                                        @if($isBookedStatus)
                                                            Забронировано
                                                        @elseif($isCanceled)
                                                            Отменено
                                                        @elseif($isBusy)
                                                            Занято
                                                        @elseif($isAvailable)
                                                            Свободно
                                                        @else
                                                            Не создан
                                                        @endif
                                                    </div>
                                                </button>
                                                
                                                {{-- Выпадающее меню --}}
                                                @if($isMenuOpen)
                                                    <div class="absolute z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg min-w-[150px]"
                                                         style="left: 50%; transform: translateX(-50%);">
                                                        <div class="py-1">
                                                            <button wire:click="setSlotStatus('{{ $slot['time'] }}', 'available')"
                                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-green-50 text-gray-700 flex items-center">
                                                                <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                                                                Свободно
                                                            </button>
                                                            <button wire:click="setSlotStatus('{{ $slot['time'] }}', 'booked')"
                                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-purple-50 text-gray-700 flex items-center">
                                                                <span class="w-3 h-3 rounded-full bg-purple-500 mr-2"></span>
                                                                Забронировано
                                                            </button>
                                                            <button wire:click="setSlotStatus('{{ $slot['time'] }}', 'busy')"
                                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-gray-700 flex items-center">
                                                                <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                                                                Занято
                                                            </button>
                                                            <button wire:click="setSlotStatus('{{ $slot['time'] }}', 'canceled')"
                                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 text-gray-700 flex items-center">
                                                                <span class="w-3 h-3 rounded-full bg-gray-400 mr-2"></span>
                                                                Отменено
                                                            </button>
                                                            @if($hasSlot)
                                                                <div class="border-t border-gray-200 my-1"></div>
                                                                <button wire:click="setSlotStatus('{{ $slot['time'] }}', 'delete')"
                                                                        class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-red-600 flex items-center">
                                                                    <span class="w-3 h-3 rounded-full border-2 border-red-500 mr-2"></span>
                                                                    Удалить слот
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-4 text-center text-xs text-gray-600 space-y-1">
                                    <p><strong>Инструкция:</strong></p>
                                    <p>• Нажмите на слот времени, чтобы открыть меню выбора статуса</p>
                                    <p>• Выберите нужный статус из выпадающего меню</p>
                                    <p>• Кнопка "Сделать весь день рабочим" создаст только не созданные слоты свободными</p>
                                </div>
                                
                                {{-- Обработчик клика вне меню для его закрытия --}}
                                @if($selectedSlotForMenu)
                                    <div wire:click="closeSlotMenu" 
                                         class="fixed inset-0 z-40" 
                                         style="background: transparent;"></div>
                                @endif
                            @else
                                <div class="text-center py-4 text-gray-500 text-sm">
                                    Нет доступных временных слотов
                                </div>
                            @endif
                        </div>
                    @elseif($master_id && !$date)
                        <div class="mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg p-4 shadow-inner">
                            <div class="text-center py-4 text-gray-500 text-sm">
                                Выберите дату для отображения временных слотов
                            </div>
                        </div>
                    @else
                        <div class="mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg p-4 shadow-inner">
                            <div class="text-center py-4 text-gray-500 text-sm">
                                Выберите мастера для продолжения
                            </div>
                        </div>
                    @endif

                    {{-- Кнопки --}}
                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" wire:click="closeModal"
                                class="bg-gray-300 px-3 py-1 rounded text-sm hover:bg-gray-400">
                            Закрыть
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif

    <div class="mt-4">
        {{ $schedules->links('pagination.schedules') }}
    </div>
</div>
