<div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow space-y-6 mt-10">
    <h2 class="text-xl font-bold text-center text-gray-900">Запись к мастеру</h2>

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Основная форма -->
    <form wire:submit.prevent="proceedToPayment" class="space-y-5 px-4">
        <!-- Ваши поля формы остаются без изменений -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Имя *</label>
            <input type="text" wire:model="client_name" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('client_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Телефон *</label>
            <input type="text" wire:model="phone" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Электронная почта *</label>
            <input type="email" wire:model="email"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Услуга *</label>
            <select wire:model="selectedService" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Выберите услугу</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}">
                        {{ $service->name }} ({{ $service->duration }} мин.) - {{ $service->price }} ₽
                    </option>
                @endforeach
            </select>
            @error('selectedService') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Мастер *</label>
            <select wire:model="master_id" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Выберите мастера</option>
                @foreach($masters as $master)
                    <option value="{{ $master->id }}" 
                        {{ $master->id == $route_master_id ? 'selected' : '' }}>
                        {{ $master->name }}
                    </option>
                @endforeach
            </select>
            @error('master_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Дата *</label>
                <input type="date" wire:model="selectedDate" required
                       min="{{ now()->format('Y-m-d') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('selectedDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Время *</label>
                @if($selectedDate && $master_id)
                    @if($selectedTime)
                        <div class="p-4 bg-blue-50 border-2 border-blue-500 rounded-lg text-center">
                            <p class="text-lg font-semibold text-blue-700">
                                {{ $selectedTime }} 
                                @if($bookingEndTime && $bookingEndTime != $selectedTime)
                                    - {{ $bookingEndTime }}
                                @endif
                            </p>
                            <button type="button" wire:click="$set('selectedTime', '')"
                                    class="mt-2 text-sm text-blue-600 hover:text-blue-800 underline">
                                Изменить время
                            </button>
                        </div>
                    @else
                        <div class="grid grid-cols-3 gap-2 max-h-40 overflow-y-auto p-2 border rounded-lg">
                            @foreach($this->timeSlots as $slot)
                                <button type="button" wire:click="selectTimeSlot('{{ $slot['time'] }}')"
                                        class="p-2 text-sm border rounded transition duration-200
                                            {{ $slot['busy'] ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-blue-50 hover:border-blue-300' }}"
                                        @if($slot['busy']) disabled @endif>
                                    {{ $slot['time'] }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @error('selectedTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                @else
                    <div class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500">
                        Сначала выберите дату и мастера
                    </div>
                @endif
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Комментарий</label>
            <textarea wire:model="notes" rows="4"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        @if($depositAmount > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-800 font-semibold">
                    Оплата: <span class="text-lg">{{ $depositAmount }} ₽</span>
                </p>
                <p class="text-yellow-600 text-sm mt-1">
                    
                </p>
            </div>
        @endif

        <div class="pt-2 flex justify-between">
            <a href="/schedule-browser"
               class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200 font-medium">
                Назад к расписанию
            </a>
            
            <button type="submit"
                    wire:loading.attr="disabled"
                    style="background-color: #3b82f6; color: white; border-radius: 8px;padding: 4px 12px;">
                <span wire:loading.remove>Перейти к оплате</span>
                <span wire:loading>
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Проверка...
                </span>
            </button>
        </div>
    </form>

    <!-- Модальное окно оплаты -->
    @if ($showPaymentModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(5px);">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-auto overflow-hidden border border-gray-200">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
                


<div class="flex justify-center gap-2 mb-4">
    <img src="https://cdn.forumspb.com/upload/iblock/4b6/fpwpk5swo3ayfsbudujg1hlvvn66jsyi.png?171718612638008" style="width:177px; height:95px;">
</div>



<div class="text-center mb-4">
<p style="color: black;">Сумма к оплате</p>
<p class="text-2xl font-bold" style="color: black;">{{ $depositAmount }} ₽</p>
</div>

            <!-- Форма оплаты -->
            <div class="p-6 bg-white" style="color: black;">
                @if (!$showPaymentProcessing)
                <form wire:submit.prevent="processPayment" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Имя владельца карты</label>
                        <input type="text" wire:model="cardHolder" placeholder="IVAN IVANOV"
                               class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('cardHolder') <span class="text-red-500 text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Номер карты</label>
                        <input type="text" wire:model="cardNumber" placeholder="1234 5678 9012 3456" 
                               maxlength="19"
                               class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('cardNumber') <span class="text-red-500 text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Дата истечения срока действия</label>
                            <input type="text" wire:model="cardExpiry" placeholder="MM / YY" maxlength="5"
                                   class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('cardExpiry') <span class="text-red-500 text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Security code</label>
                            <input type="text" wire:model="cardCvv" placeholder="123" maxlength="3"
                                   class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('cardCvv') <span class="text-red-500 text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>



                    <!-- Тестовые данные -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-4">
                        <p class="text-xs text-blue-800 font-medium text-center">
                             Test card: 4111 1111 1111 1111 | 12/34 | 123
                        </p>
                    </div>

                    <button type="submit"
                            class="text-center" style="background-color: #3b82f6; color: white; border-radius: 8px;padding: 4px 12px;" >
                        Оплатить {{ $depositAmount }}
                    </button>

                    <button type="button" wire:click="closePaymentModal"
                            class="px-3 py-1 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200 font-medium">
                        Отмена
                    </button>
                </form>
                @else
                <div class="text-center py-8 bg-white rounded-lg border border-gray-200">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto mb-4"></div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Processing Payment</h4>
                    <p class="text-gray-600 font-medium">Please wait...</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Модальное окно успешной оплаты -->
@if ($showSuccessModal)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(5px);">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-auto overflow-hidden border border-gray-200" style="color: black;">
        <!-- Заголовок с иконкой успеха -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 text-white text-center">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="font-semibold text-green-800 text-lg mb-3" style="color: black;">Оплата прошла успешно!</h2>
            <p class="text-gray-600">Ваша запись подтверждена</p>
        </div>

        <!-- Детали бронирования -->
        <div class="p-6 space-y-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h3 class="font-semibold text-green-800 text-lg mb-3">Детали записи:</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Услуга:</span>
                        <span class="font-medium">{{ $bookingDetails['service_name'] ?? '' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Стоимость:</span>
                        <span class="font-medium">{{ $bookingDetails['service_price'] ?? 0 }} ₽</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Депозит оплачен:</span>
                        <span class="font-medium text-green-600">{{ $bookingDetails['deposit_amount'] ?? 0 }} ₽</span>
                    </div>
                    <div class="border-t border-green-200 pt-2 mt-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Мастер:</span>
                            <span class="font-medium">{{ $bookingDetails['master_name'] ?? '' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Дата:</span>
                            <span class="font-medium">{{ isset($bookingDetails['date']) ? \Carbon\Carbon::parse($bookingDetails['date'])->format('d.m.Y') : '' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Время:</span>
                            <span class="font-medium">{{ $bookingDetails['time'] ?? '' }} - {{ $bookingDetails['time_end'] ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-blue-800 text-sm">
                        <strong>Важно:</strong> Не забудьте подойти за 5 минут до назначенного времени
                    </p>
                </div>
            </div>
        </div>

        <!-- Кнопка возврата -->
        <div class="px-6 pb-6">
            <button class="flex" wire:click="closeSuccessModal" 
                    style="background-color: #3b82f6; color: white; border-radius: 8px;padding: 4px 12px;" >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                
                Вернуться к расписанию
            </button>
            <p class="text-center text-gray-500 text-sm mt-3">
                
            </p>
        </div>
    </div>
</div>
@endif
</div>

<script>
document.addEventListener('livewire:init', () => {
    // Блокировка скролла при открытии модальных окон
    Livewire.hook('element.updated', (el, component) => {
        if (component.showPaymentModal || component.showSuccessModal) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    });

    // Обработка события обработки платежа
    Livewire.on('payment-processing', () => {
        // Имитация обработки платежа с задержкой
        setTimeout(() => {
            Livewire.dispatch('complete-payment');
        }, 3000); // Увеличил задержку для лучшего UX
    });
});

// Добавляем обработчик для complete-payment
document.addEventListener('livewire:init', () => {
    Livewire.on('complete-payment', () => {
        @this.completePayment();
    });
});

// Автоматическое форматирование номера карты
document.addEventListener('livewire:initialized', () => {
    const cardNumberInput = document.querySelector('input[wire\\:model="cardNumber"]');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let matches = value.match(/\d{4,16}/g);
            let match = matches && matches[0] || '';
            let parts = [];
            
            for (let i = 0, len = match.length; i < len; i += 4) {
                parts.push(match.substring(i, i + 4));
            }
            
            if (parts.length) {
                e.target.value = parts.join(' ');
            } else {
                e.target.value = value;
            }
        });
    }
});
</script>