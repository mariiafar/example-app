<div class="max-w-2xl mx-auto p-6 bg-white text-gray-900 dark:bg-gray-900 dark:text-white shadow-lg rounded-lg">
    

    {{-- Кнопка создания только для администратора --}}
    @if(auth()->user()->role === 'admin')
        <div>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-gray-900 dark:text-white px-4 py-2 rounded mb-4">
                + Добавить пользователя
            </button>
        </div>
    @endif

    {{-- Панель поиска и фильтров --}}
    <div class="mb-6 space-y-4">
        <input type="text" wire:model.live="search" placeholder="Поиск по имени..."
               class="w-full p-2 border rounded text-gray-900 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row gap-4">
            <select wire:model.live="filterStatus" class="p-2 border rounded text-gray-900 dark:bg-gray-800 dark:border-gray-700">
                <option value="all">Все пользователи</option>
                <option value="active">Только активные</option>
                <option value="trashed">Только удалённые</option>
            </select>

            <select wire:model.live="filterRole" class="p-2 border rounded text-gray-900 dark:bg-gray-800 dark:border-gray-700"
>
                <option value="all">Все роли</option>
                @if(auth()->user()->role === 'admin')
                    <option value="admin">Администраторы</option>
                @endif
                <option value="master">Мастера</option>
                <option value="client">Клиенты</option>
            </select>

            <select wire:model.live="sortField" class="p-2 border rounded text-gray-900 dark:bg-gray-800 dark:border-gray-700">
                <option value="name">Сортировать по имени</option>
                <option value="created_at">Сортировать по дате</option>
                <option value="email">Сортировать по email</option>
            </select>

            <select wire:model.live="sortDirection" class="p-2 border rounded text-gray-900 dark:bg-gray-800 dark:border-gray-700">
                <option value="asc">По возрастанию</option>
                <option value="desc">По убыванию</option>
            </select>
        </div>
    </div>

    {{-- Форма создания/редактирования --}}
    @if($isOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="max-w-2xl mx-auto p-6 bg-white dark:bg-gray-900 dark:text-white shadow-lg rounded-lg">
                <h2 class="bg-blue-600 hover:bg-blue-700 text-black px-3 py-1 rounded">
                    {{ $user_id ? 'Редактировать пользователя' : 'Создать пользователя' }}
                </h2>

                <input type="text" wire:model="name" placeholder="Имя" class="w-full mb-3 p-2 border rounded text-gray-900">
                @error('name') <p class="text-red-500">{{ $message }}</p> @enderror

                <input type="email" wire:model="email" placeholder="Email" class="w-full mt-2 mb-3 p-2 border rounded text-gray-900">
                @error('email') <p class="text-red-500">{{ $message }}</p> @enderror

                <input type="password" wire:model="password" placeholder="Пароль" class="w-full mt-2 mb-3 p-2 border rounded text-gray-900">
                @error('password') <p class="text-red-500">{{ $message }}</p> @enderror

                {{-- ВЫБОР РОЛИ (только для администратора) --}}
                @if(auth()->user()->role === 'admin')
                    <label class="block mb-1">Роль</label>
                    <select wire:model="role" class="w-full p-2 border rounded text-gray-900 dark:bg-gray-700 dark:border-gray-600 mb-4">
                        <option value="">Выберите роль</option>
                        <option value="client">Клиент</option>
                        <option value="master">Мастер</option>
                        <option value="admin">Администратор</option>
                    </select>
                    @error('role') <p class="text-red-500">{{ $message }}</p> @enderror
                @endif

                <div class="mt-4 flex justify-between">
                    <button wire:click="closeModal" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                        Отмена
                    </button>
                    <button wire:click="{{ $user_id ? 'update' : 'store' }}" style="background-color: #3b82f6; color: white;" class="px-3 py-1 rounded">
                        {{ $user_id ? 'Обновить' : 'Создать' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Таблица пользователей --}}
    <table class="w-full border-collapse border border-gray-300 rounded-lg">
        <thead>
            <tr class="bg-gray-200 text-gray-100">
                <th class="border p-3">Имя</th>
                <th class="border p-3">Email</th>
                <th class="border p-3">Роль</th>
                <th class="border p-3">Дата создания</th>
                <th class="border p-3">Дата удаления</th>
                <th class="border p-3">Действия</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr class="text-center">
                    <td class="border p-3">{{ $user->name }}</td>
                    <td class="border p-3">{{ $user->email }}</td>
                    <td class="border p-3 capitalize">{{ $user->role }}</td>
                    <td class="border p-3">{{ $user->created_at }}</td>
                    <td class="border p-3">{{ $user->deleted_at ?? 'Пока не удален' }}</td>
                    <td class="border p-3 space-x-6">
                        <button wire:click="edit({{ $user->id }})" style="background-color: #3b82f6; color: white;" class="px-3 py-1 rounded">
                            Редактировать
                        </button>

                        @if(auth()->user()->role === 'admin')
                            @if(!$user->deleted_at)
                                <button wire:click="delete({{ $user->id }})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                    Удалить
                                </button>
                            @else
                                <button wire:click="restoreUser({{ $user->id }})" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">
                                    Восстановить
                                </button>
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="border p-3 text-center text-gray-500">Нет пользователей</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    
    <div class="mt-4">
        {{ $users->links('pagination.schedules') }}
    </div>
</div>