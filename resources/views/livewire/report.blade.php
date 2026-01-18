<div class="max-w-5xl mx-auto p-6 bg-white rounded shadow mt-8 space-y-8">
    
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 text-center">
        <div class="bg-blue-100 p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-blue-700">Всего заявок</h3>
            <p class="text-2xl font-bold text-blue-900">{{ $totalApplications }}</p>
        </div>
        <div class="bg-green-100 p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-green-700">Общая выручка</h3>
            <p class="text-2xl font-bold text-green-900">{{ number_format($totalRevenue, 0, ',', ' ') }} ₽</p>
        </div>
        <div class="bg-yellow-100 p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-yellow-700">Оставлено отзывов</h3>
            <p class="text-2xl font-bold text-yellow-900">{{ $reviewsCount }}</p>
        </div>
        <div class="bg-purple-100 p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-purple-700">ТОП-услуги</h3>
            <div class="mt-2 space-y-1 text-left max-h-32 overflow-y-auto pr-1">
                @foreach($topServices as $service)
                    <div class="flex justify-between items-center text-sm py-1 border-b border-purple-200 last:border-b-0">
                        <span class="truncate mr-2 flex-1" title="{{ $service['name'] }}">
                            {{ $loop->iteration }}. {{ $service['name'] }}
                        </span>
                        <span class="font-semibold bg-purple-200 text-purple-800 px-2 py-0.5 rounded whitespace-nowrap ml-2">
                            {{ $service['count'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-gray-100 p-4 rounded shadow mt-10">
        <h4 class="text-lg font-bold mb-4 text-gray-700">Периодический отчет</h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <input type="date" wire:model="reportStartDate" class="p-2 border rounded" placeholder="Дата начала">
            <input type="date" wire:model="reportEndDate" class="p-2 border rounded" placeholder="Дата окончания">
            <select wire:model="reportMaster" class="p-2 border rounded">
                <option value="">Все мастера</option>
                @foreach ($topMasters as $master => $count)
                    <option value="{{ $master }}">{{ $master }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-4 text-right">
            <button wire:click="openCustomReport"
                    style="background-color: #3b82f6; color: white;" class="px-3 py-1 rounded">
                Сформировать отчет
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border rounded p-4 shadow">
            <h4 class="text-lg font-bold mb-4 text-gray-700">Популярные дни недели</h4>
            <ul class="space-y-2">
                @php
                $dayTranslations = [
                    'Monday' => 'Понедельник',
                    'Tuesday' => 'Вторник',
                    'Wednesday' => 'Среда',
                    'Thursday' => 'Четверг',
                    'Friday' => 'Пятница',
                    'Saturday' => 'Суббота',
                    'Sunday' => 'Воскресенье',
                ];
                @endphp

                @foreach ($topDays as $day => $count)
                    <li class="flex justify-between">
                        <span>{{ $dayTranslations[$day] ?? $day }}</span>
                        <span class="font-semibold">{{ $count }} заявок</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white border rounded p-4 shadow">
            <h4 class="text-lg font-bold mb-4 text-gray-700">Топ мастеров</h4>
            <ul class="space-y-2">
                @foreach ($topMasters as $master => $count)
                    <li class="flex justify-between">
                        <span>{{ $master }}</span>
                        <span class="font-semibold">{{ $count }} заявок</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10">
        <div class="bg-white border rounded p-4 shadow">
            <h4 class="text-lg font-bold mb-4 text-gray-700">Популярные часы записей</h4>
            <ul class="space-y-2">
                @foreach ($popularHours as $hour => $count)
                    <li class="flex justify-between">
                        <span>{{ sprintf('%02d', $hour) }}:00 — {{ sprintf('%02d', $hour + 1) }}:00</span>
                        <span class="font-semibold">{{ $count }} записей</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white border rounded p-4 shadow">
            <h4 class="text-lg font-bold mb-4 text-gray-700">Статистика клиентов</h4>
            <ul class="space-y-2 text-gray-700">
                <li class="flex justify-between">
                    <span>Новые клиенты</span>
                    <span class="font-semibold">{{ $newClientsCount }}</span>
                </li>
                <li class="flex justify-between">
                    <span>Повторные записи</span>
                    <span class="font-semibold">{{ $repeatClientsCount }}</span>
                </li>
            </ul>
        </div>
    </div>

    @if ($showReportModal)
    <div class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center px-2">
        <div class="bg-white w-full max-w-md p-6 rounded-lg shadow-lg border border-gray-300 text-center text-sm font-sans space-y-4">
            <h2 class="text-lg font-bold text-gray-800">ИТОГО по выбранному периоду</h2>

            <div class="space-y-1 text-gray-700">
                <p><strong>Мастер:</strong> {{ $reportData['master'] }}</p>
                <p><strong>Период:</strong> {{ $reportData['period']['start'] }} — {{ $reportData['period']['end'] }}</p>
                <p><strong>Записей:</strong> {{ $reportData['totals']['applications'] }}</p>
                <p><strong>Выручка:</strong> {{ number_format($reportData['totals']['revenue'], 0, ',', ' ') }} ₽</p>
                <p><strong>Средний чек:</strong> {{ number_format($reportData['totals']['average_check'], 0, ',', ' ') }} ₽</p>
            </div>

            <div class="text-left text-gray-800">
                <h3 class="font-semibold mt-4 mb-2">ТОП-услуги</h3>
                @if(count($reportData['services']) > 0)
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($reportData['services'] as $service => $stat)
                            <li>
                                <span class="font-medium">{{ $service }}</span> – 
                                <span class="text-blue-600 font-semibold">{{ $stat['count'] }} шт</span> / 
                                <span class="text-green-600 font-semibold">{{ number_format($stat['revenue'], 0, ',', ' ') }} ₽</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500 italic">Нет данных об услугах</p>
                @endif
            </div>

            <div class="text-left text-gray-800">
                <h3 class="font-semibold mt-4 mb-2">Клиенты</h3>
                <ul class="space-y-1">
                    <li>Новые: {{ $reportData['clients']['new'] }} ({{ $reportData['clients']['new_percent'] }}%)</li>
                    <li>Повторные: {{ $reportData['clients']['repeat'] }} ({{ $reportData['clients']['repeat_percent'] }}%)</li>
                    <li>Отмены: {{ $reportData['canceled'] }} ({{ round(($reportData['canceled'] / max($reportData['totals']['applications'], 1)) * 100, 1) }}%)</li>
                </ul>
            </div>

            <div class="flex flex-col gap-2 items-center mt-4">
                <button wire:click="closeCustomReport"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm px-4 py-1 rounded mt-2">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
