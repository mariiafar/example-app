<div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-lg">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Мастера и их услуги</h1>
        <p class="text-gray-600">Управление услугами, которые выполняют мастера</p>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Поиск -->
    <div class="mb-6">
        <input 
            type="text" 
            wire:model.live="search" 
            placeholder="Поиск по имени мастера..." 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
    </div>

    <!-- Таблица мастеров -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg mx-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Имя мастера
                    </th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Действия
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($masters as $master)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $master->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $master->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button 
                                wire:click="openEditModal({{ $master->id }})"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-black rounded-lg transition duration-200 font-medium">
                                Редактировать услуги
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                            Мастера не найдены
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Пагинация -->
    <div class="mt-6">
        {{ $masters->links('pagination.schedules') }}
    </div>

    <!-- Модальное окно редактирования услуг -->
    @if($isEditModalOpen && $selectedMaster)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full max-h-[80vh] overflow-hidden flex flex-col">
                <!-- Заголовок модального окна -->
                <div class="bg-gray-50 border-b border-gray-200 p-4 flex-shrink-0">
                    <h2 class="text-xl font-bold mb-1 text-black">Редактирование услуг</h2>
                    <p class="text-black text-sm">{{ $selectedMaster->name }}</p>
                </div>

                <!-- Содержимое модального окна с прокруткой -->
                <div class="p-4 flex-1 overflow-y-auto" style="max-height: calc(80vh - 180px);">
                    <p class="text-gray-600 mb-3 text-sm">Выберите услуги мастера:</p>
                    
                    <div class="space-y-2">
                        @foreach($availableServices as $service)
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                <input 
                                    type="checkbox" 
                                    wire:model="masterServices" 
                                    value="{{ $service->id }}"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                >
                                <div class="ml-3 flex-1">
                                    <div class="font-medium text-gray-900 text-sm">{{ $service->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $service->duration }} мин. • {{ number_format($service->price, 0, ',', ' ') }} ₽
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2 flex-shrink-0">
                    <button 
                        wire:click="closeEditModal"
                        class="px-4 py-2 border-2 border-gray-300 rounded-lg text-black hover:bg-gray-50 transition duration-200 font-medium text-sm">
                        Отмена
                    </button>
                    <button 
                        wire:click="saveMasterServices"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-black rounded-lg transition duration-200 font-medium text-sm">
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
