<div class="max-w-6xl mx-auto p-4 bg-white shadow-lg rounded-lg">
    
    {{-- Панель фильтрации --}}
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-4 gap-2">
        {{-- Поиск по мастеру --}}
        <input 
            type="text" 
            wire:model.live="search" 
            placeholder="Поиск по мастеру..." 
            class="w-full p-1.5 border border-gray-300 rounded text-sm shadow-sm"
        >

        {{-- Фильтр по статусу --}}
        <select wire:model.live="filterStatus" class="w-full p-1.5 border rounded text-sm shadow-sm">
            <option value="all">Все статусы</option>
            <option value="available">Свободно</option>
            <option value="busy">Занято</option>
            <option value="canceled">Отменено</option>
        </select>

        {{-- Сортировка по полю --}}
        <select wire:model.live="sortField" class="w-full p-1.5 border rounded text-sm shadow-sm">
            <option value="date">По дате</option>
            <option value="time_start">По времени</option>
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
                    <th class="border p-2">Мастер</th>
                    <th class="border p-2">Дата</th>
                    <th class="border p-2">Время</th>
                    <th class="border p-2">Статус</th>
                    <th class="border p-2">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schedules as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 text-xs">
                        <td class="border p-2">{{ $item->master?->name ?? '—' }}</td>
                        <td class="border p-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($item->date)->format('d.m.Y') }}</td>
                        <td class="border p-2">{{ $item->time_start }} — {{ $item->time_end }}</td>
                        <td class="border p-2">
                            @php
                                $statusLabels = [
                                    'available' => ['Свободно', 'text-green-800 bg-green-200'],
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
                        <td colspan="5" class="border p-3 text-center text-gray-500 text-sm">Нет записей в расписании</td>
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

            <form wire:submit.prevent="{{ $schedule_id ? 'update' : 'store' }}">
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

                <div x-data="{
    status: @entangle('status'),
    scheduleId: @entangle('schedule_id'),
    currentDate: '{{ now()->format('Y-m-d') }}',
    defaultStartTime: '09:00',
    defaultEndTime: '19:30',
    
    init() {
        if (this.status === 'available' && !this.scheduleId) {
            this.$wire.set('date', this.currentDate);
            this.$wire.set('time_start', this.defaultStartTime);
            this.$wire.set('time_end', this.defaultEndTime);
        }
        
        this.$watch('status', (newStatus) => {
            if (newStatus === 'available' && !this.scheduleId) {
                this.$wire.set('date', this.currentDate);
                this.$wire.set('time_start', this.defaultStartTime);
                this.$wire.set('time_end', this.defaultEndTime);
            }
        });
        
        this.$watch('scheduleId', (newScheduleId) => {
            if (this.status === 'available' && !newScheduleId) {
                this.$wire.set('date', this.currentDate);
                this.$wire.set('time_start', this.defaultStartTime);
                this.$wire.set('time_end', this.defaultEndTime);
            }
        });
    }
}">

{{-- Дата --}}
<div class="mb-3">
    <label class="block mb-1 text-xs font-medium">Дата</label>
    <template x-if="status">
        <input type="date" 
               wire:model="date" 
               class="w-full p-1.5 border rounded text-sm">
    </template>
    @error('date') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
</div>

{{-- Время --}}
<div class="mb-3 grid grid-cols-2 gap-3">
    <div>
        <label class="block mb-1 text-xs font-medium">Начало</label>
        <template x-if="status === 'available' && !scheduleId">
            <div>
                <input type="text" 
                       :value="defaultStartTime" 
                       disabled 
                       class="w-full p-1.5 border rounded text-sm bg-gray-100">
                <input type="hidden" wire:model="time_start" :value="defaultStartTime">
            </div>
        </template>
        <template x-if="!(status === 'available' && !scheduleId)">
            <select wire:model="time_start" class="w-full p-1.5 border rounded text-sm">
                <option value="">Выберите время</option>
                @foreach($timeSlots as $slot)
                    <option value="{{ $slot }}" 
                            :selected="status === 'available' && !scheduleId && '{{ $slot }}' === defaultStartTime">
                        {{ $slot }}
                    </option>
                @endforeach
            </select>
        </template>
        @error('time_start') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
    </div>
    <div>
        <label class="block mb-1 text-xs font-medium">Конец</label>
        <template x-if="status === 'available' && !scheduleId">
            <div>
                <input type="text" 
                       :value="defaultEndTime" 
                       disabled 
                       class="w-full p-1.5 border rounded text-sm bg-gray-100">
                <input type="hidden" wire:model="time_end" :value="defaultEndTime">
            </div>
        </template>
        <template x-if="!(status === 'available' && !scheduleId)">
            <select wire:model="time_end" class="w-full p-1.5 border rounded text-sm">
                <option value="">Выберите время</option>
                @foreach($timeSlots as $slot)
                    <option value="{{ $slot }}" 
                            :selected="status === 'available' && !scheduleId && '{{ $slot }}' === defaultEndTime">
                        {{ $slot }}
                    </option>
                @endforeach
            </select>
        </template>
        @error('time_end') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
    </div>
</div>

{{-- Статус --}}
<div class="mb-3">
    <label class="block mb-1 text-xs font-medium">Статус</label>
    <select wire:model="status" class="w-full p-1.5 border rounded text-sm">
        <option value="available">Свободно</option>
        <option value="busy">Занято</option>
    </select>
    @error('status') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
</div>

</div>

                {{-- Кнопки --}}
                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" wire:click="closeModal"
                            class="bg-gray-300 px-3 py-1 rounded text-sm hover:bg-gray-400">
                        Отмена
                    </button>
                    <button type="submit"
                            style="background-color: #3b82f6; color: white;" class="px-3 py-1 rounded text-sm">
                        {{ $schedule_id ? 'Обновить' : 'Сохранить' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="mt-4">
        {{ $schedules->links('pagination.schedules') }}
    </div>
</div>
