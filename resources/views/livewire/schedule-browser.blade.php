<div class="p-6 space-y-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="grid grid-cols-1 gap-6">
        @foreach ($masters as $master)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 min-h-[140px] flex flex-col space-y-4">
                <div class="flex items-center justify-between">
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-200">
                        {{ $master->name }}
                    </div>
                    <button 
                        wire:click="toggleMaster({{ $master->id }})"
                        style="background-color: #3b82f6; color: white;" 
                        class="px-3 py-1 rounded">
                        {{ $openedMaster === $master->id ? 'Скрыть' : 'Записаться' }}
                    </button>
                </div>

                @if ($openedMaster === $master->id)
                    <div class="mt-2 border-t border-gray-300 dark:border-gray-700 pt-4">
                        {{-- Календарь --}}
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 shadow-inner space-y-3">
                            <div class="flex items-center justify-between mb-2">
                                <button wire:click="previousMonth" class="text-xl font-bold">←</button>
                                <h4 class="text-md font-medium">
                                    {{ \Carbon\Carbon::parse($currentMonth)->locale('ru')->isoFormat('MMMM YYYY') }}
                                </h4>
                                <button wire:click="nextMonth" class="text-xl font-bold">→</button>
                            </div>

                            @php
                                $startOfMonth = \Carbon\Carbon::parse($currentMonth);
                                $endOfMonth = $startOfMonth->copy()->endOfMonth();
                                $daysInMonth = $endOfMonth->day;
                                $availableDates = $availableDatesByMaster[$master->id] ?? [];
                            @endphp

                            <div class="flex flex-wrap justify-center gap-4 mx-auto max-w-3xl">
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $date = $startOfMonth->copy()->day($day)->format('Y-m-d');
                                        $hasSchedule = in_array($date, $availableDates);
                                        $isToday = $date === \Carbon\Carbon::today()->format('Y-m-d');
                                        $isSelected = $selectedDate === $date;
                                    @endphp

                                    <button
                                        class="relative w-16 h-16 flex items-center justify-center rounded transition-colors
                                            {{ $isToday ? 'ring-2 ring-blue-500' : '' }}
                                            {{ $isSelected ? 'bg-blue-600 text-white' : '' }}
                                            {{ $hasSchedule && !$isSelected ? 'bg-white hover:bg-blue-50 border border-gray-300' : '' }}
                                            {{ !$hasSchedule ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : '' }}"
                                        @if ($hasSchedule)
                                            wire:click="selectDate('{{ $date }}')"
                                        @else
                                            disabled
                                        @endif
                                    >
                                        {{ $day }}
                                        @if ($hasSchedule)
                                            <span class="absolute bottom-2 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-green-500 rounded-full"></span>
                                        @endif
                                    </button>
                                @endfor
                            </div>
                        </div>

                        {{-- Тайм-слоты --}}
@if ($selectedDate)
    <div class="mt-4 bg-gray-100 dark:bg-gray-700 rounded-lg p-6 shadow-inner">
        <h4 class="text-md font-medium mb-6 text-center">
            Время на {{ \Carbon\Carbon::parse($selectedDate)->format('d.m.Y') }}
        </h4>

        @if(count($timeSlots) > 0)
            @php
                $half = ceil(count($timeSlots) / 2);
                $row1 = array_slice($timeSlots, 0, $half);
                $row2 = array_slice($timeSlots, $half);
            @endphp

            <div class="space-y-6">
                {{-- Первая строка --}}
                <div class="flex gap-4 justify-center flex-wrap">
                    @foreach ($row1 as $slot)
                        @if (auth()->user()->role === 'client')
                            @if ($slot['available'] && !$slot['busy'])
                                <button
                                    wire:click="selectTimeSlot('{{ $slot['time'] }}')"
                                    class="px-4 py-3 rounded-lg border-2 border-gray-300 bg-white text-gray-800 
                                           hover:bg-blue-50 hover:border-blue-400 cursor-pointer transition-all duration-200 
                                           text-sm font-medium shadow-sm hover:shadow-md min-w-[80px] text-center">
                                    {{ $slot['time'] }}
                                </button>
                            @else
                                <div class="px-4 py-3 rounded-lg border-2 border-red-500 bg-red-100 text-red-700 
                                            cursor-not-allowed text-sm font-medium shadow-sm min-w-[80px] text-center">
                                    {{ $slot['time'] }}
                                    <div class="text-xs text-red-600 mt-1">
                                        {{ $slot['isBooked'] ? 'Забронировано' : 'Занято' }}
                                    </div>
                                </div>
                            @endif
                        @else
                            @if ($slot['available'] && !$slot['busy'])
                                <div class="px-4 py-3 rounded-lg border-2 border-green-300 bg-white text-gray-800 
                                            text-sm font-medium shadow-sm min-w-[80px] text-center">
                                    {{ $slot['time'] }}
                                    <div class="text-xs text-green-600 mt-1">Свободно</div>
                                </div>
                            @else
                                <div class="px-4 py-3 rounded-lg border-2 border-red-500 bg-red-100 text-red-700 
                                            text-sm font-medium shadow-sm min-w-[80px] text-center">
                                    {{ $slot['time'] }}
                                    <div class="text-xs text-red-600 mt-1">
                                        {{ $slot['isBooked'] ? 'Забронировано' : 'Занято' }}
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endforeach
                </div>

                {{-- Вторая строка --}}
                <div class="flex gap-4 justify-center flex-wrap">
                    @foreach ($row2 as $slot)
                        @if (auth()->user()->role === 'client')
                            @if ($slot['available'] && !$slot['busy'])
                                <button
                                    wire:click="selectTimeSlot('{{ $slot['time'] }}')"
                                    class="px-4 py-3 rounded-lg border-2 border-gray-300 bg-white text-gray-800 
                                           hover:bg-blue-50 hover:border-blue-400 cursor-pointer transition-all duration-200 
                                           text-sm font-medium shadow-sm hover:shadow-md min-w-[80px] text-center">
                                    {{ $slot['time'] }}
                                </button>
                            @else
                                <div class="px-4 py-3 rounded-lg border-2 border-red-500 bg-red-100 text-red-700 
                                            cursor-not-allowed text-sm font-medium shadow-sm min-w-[80px] text-center">
                                    {{ $slot['time'] }}
                                    <div class="text-xs text-red-600 mt-1">
                                        {{ $slot['isBooked'] ? 'Забронировано' : 'Занято' }}
                                    </div>
                                </div>
                            @endif
                        @else
                            @if ($slot['available'] && !$slot['busy'])
                                <div class="px-4 py-3 rounded-lg border-2 border-green-300 bg-white text-gray-800 
                                            text-sm font-medium shadow-sm min-w-[80px] text-center">
                                    {{ $slot['time'] }}
                                    <div class="text-xs text-green-600 mt-1">Свободно</div>
                                </div>
                            @else
                                <div class="px-4 py-3 rounded-lg border-2 border-red-500 bg-red-100 text-red-700 
                                            text-sm font-medium shadow-sm min-w-[80px] text-center">
                                    {{ $slot['time'] }}
                                    <div class="text-xs text-red-600 mt-1">
                                        {{ $slot['isBooked'] ? 'Забронировано' : 'Занято' }}
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>

            
            
            {{-- Информация для клиентов --}}
            @if(auth()->user()->role === 'client')
                <div class="mt-6 text-center text-sm text-gray-600">
                    <p>Нажмите на белые слоты для записи. Красные слоты уже заняты.</p>
                </div>
            @endif
        @else
            <div class="text-center py-4 text-gray-500">
                Нет доступных слотов времени
                @if(in_array(auth()->user()->role, ['admin', 'master']))
                    <div class="mt-4">
                        <button 
                            wire:click="createWorkingDay({{ $master->id }}, '{{ $selectedDate }}')"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded">
                            Создать рабочий день
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>