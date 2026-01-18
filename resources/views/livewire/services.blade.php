<div class="max-w-6xl mx-auto p-6 bg-white text-gray-900 dark:bg-gray-900 dark:text-white shadow-lg rounded-lg">
    

    {{-- Кнопка создания --}}
    {{-- Кнопка создания --}}
@if(auth()->user()->role !== 'client')
    <div>
        <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-gray-900 dark:text-white px-4 py-2 rounded mb-4">
            + Добавить услугу
        </button>
    </div>
@endif

@if (session('success'))
    <div class="mb-4 text-green-600 font-semibold text-center">
        {{ session('success') }}
    </div>
@endif

    {{-- Панель поиска и фильтров --}}
    <div class="mb-6 space-y-4">
        {{-- Поиск --}}
        <div>
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Поиск по названию или описанию..." 
                class="w-full p-2 border rounded text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
        </div>

        {{-- Фильтры и сортировка --}}
        <div class="flex flex-col sm:flex-row gap-4">
            {{-- Фильтр по типу услуги --}}
            <select 
                wire:model.live="filterType" 
                class="p-2 border rounded text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
                <option value="all">Все типы услуг</option>
                <option value="Татуировка">Татуировка</option>
                <option value="Пирсинг">Пирсинг</option>
                <option value="Лазерное удаление">Лазерное удаление</option>
            </select>

            {{-- Сортировка --}}
            <select 
                wire:model.live="sortField" 
                class="p-2 border rounded text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
                <option value="name">Сортировать по названию</option>
                <option value="price">Сортировать по цене</option>
                <option value="duration">Сортировать по длительности</option>
            </select>
            
            <select 
                wire:model.live="sortDirection" 
                class="p-2 border rounded text-gray-900 dark:bg-gray-800 dark:border-gray-700"
            >
                <option value="asc">По возрастанию</option>
                <option value="desc">По убыванию</option>
            </select>
        </div>
    </div>

    {{-- Форма создания/редактирования --}}
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-xl">
            <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100">
                {{ $service_id ? 'Редактировать услугу' : 'Добавить услугу' }}
            </h2>

            <form wire:submit.prevent="{{ $service_id ? 'update' : 'store' }}">
                {{-- Название услуги --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Название</label>
                    <input type="text" wire:model.defer="name" class="w-full p-2 border rounded text-gray-900">
                    @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Тип услуги --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Тип</label>
                    <select wire:model.defer="type" class="w-full p-2 border rounded text-gray-900">
                        <option value="">Выберите тип</option>
                        <option value="Татуировка">Татуировка</option>
                        <option value="Пирсинг">Пирсинг</option>
                        <option value="Лазерное удаление">Лазерное удаление</option>
                    </select>
                    @error('type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Цена --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Цена (₽)</label>
                    <input type="number" wire:model.defer="price" class="w-full p-2 border rounded text-gray-900">
                    @error('price') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Длительность --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Длительность (мин)</label>
                    <input type="number" wire:model.defer="duration" class="w-full p-2 border rounded text-gray-900">
                    @error('duration') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Описание --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Описание</label>
                    <textarea wire:model.defer="description" rows="3" class="w-full p-2 border rounded text-gray-900"></textarea>
                    @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Кнопки --}}
                <div class="flex justify-end gap-4 mt-6">
                    <button type="button" wire:click="closeModal"
                            class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                        Отмена
                    </button>
                    <button type="submit"
                            style="background-color: #3b82f6; color: white;" class="px-3 py-1 rounded">
                        {{ $service_id ? 'Обновить' : 'Сохранить' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

    <div class="overflow-x-auto text-sm">
        <table class="w-full border-collapse border border-gray-300 rounded-lg">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-xs">
                    <th class="border p-2">Название</th>
                    <th class="border p-2">Тип</th>
                    <th class="border p-2">Цена</th>
                    <th class="border p-2">Длительность</th>
                    <th class="border p-2">Описание</th>
                    @if(auth()->user()->role !== 'client')
                        <th class="border p-2">Действия</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr class="text-center text-xs">
                        <td class="border p-2">{{ $service->name }}</td>
                        <td class="border p-2">{{ $service->type }}</td>
                        <td class="border p-2 whitespace-nowrap">{{ number_format($service->price, 0, ',', ' ') }} ₽</td>
                        <td class="border p-2">{{ $service->duration }} мин</td>
                        <td class="border p-2 text-left max-w-xs whitespace-normal">
                            <div class="line-clamp-2" title="{{ $service->description }}">
                                {{ $service->description }}
                            </div>
                        </td>
                        @if(auth()->user()->role !== 'client')
                            <td class="border p-2 space-x-1 whitespace-nowrap">
                                <button wire:click="edit({{ $service->id }})"
                                        style="background-color: #3b82f6; color: white;" class="px-2 py-0.5 rounded text-xs">
                                    Ред.
                                </button>
                                @if(auth()->user()->role === 'admin')
                                    <button wire:click="delete({{ $service->id }})"
                                            class="px-2 py-0.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs">
                                        Уд.
                                    </button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role !== 'client' ? 6 : 5 }}" class="border p-3 text-center text-gray-500 text-sm">Нет услуг</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    
    <div class="mt-4">
        {{ $services->links('pagination.schedules') }}
    </div>
</div>