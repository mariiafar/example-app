<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись на татуировку | InkMaster</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-8">
        <!-- Шапка -->
        <header class="mb-12 text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Запись на татуировку</h1>
            <p class="text-xl text-gray-600">Выберите мастера и удобное время для вашего уникального рисунка</p>
        </header>

        <!-- Основной контент -->
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="md:flex">
                <!-- Форма записи -->
                <div class="p-8 w-full">
                    <!-- Шаги прогресса -->
                    <div class="mb-8">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-indigo-600">Шаг 1: Контактные данные</span>
                            <span class="text-sm font-medium text-gray-500">Шаг 2: Выбор мастера</span>
                            <span class="text-sm font-medium text-gray-500">Шаг 3: Подтверждение</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 33%"></div>
                        </div>
                    </div>

                    <!-- Форма -->
                    <form id="tattooForm" method="POST" action="/api/appointments">
                        @csrf
                        <!-- Контактные данные -->
                        <div id="step1">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Контактные данные</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Ваше имя*</label>
                                    <input type="text" id="name" name="name" required 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Телефон*</label>
                                    <input type="tel" id="phone" name="phone" required 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" id="email" name="email" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="instagram" class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">@</span>
                                        <input type="text" id="instagram" name="instagram" 
                                               class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Опишите вашу идею*</label>
                                <textarea id="description" name="description" rows="4" required 
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Размер татуировки</label>
                                <div class="grid grid-cols-3 gap-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="size" value="small" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                        <span class="ml-2 text-gray-700">Маленькая (<10см>)</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="size" value="medium" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                        <span class="ml-2 text-gray-700">Средняя (10-20см)</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="size" value="large" checked class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                        <span class="ml-2 text-gray-700">Большая (>20см)</span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="nextStep()" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Далее: Выбор мастера <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Выбор мастера и времени -->
                        <div id="step2" class="hidden">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Выбор мастера и времени</h2>
                            <div class="mb-8">
                                <label for="artist" class="block text-sm font-medium text-gray-700 mb-2">Выберите мастера*</label>
                                <select id="artist" name="artist_id" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                        onchange="loadAvailableDates(this.value)">
                                    <option value="">-- Выберите мастера --</option>
                                    <!-- Динамически загружаемые варианты -->
                                    @foreach($artists as $artist)
                                        <option value="{{ $artist->id }}" data-specialty="{{ $artist->specialty }}">{{ $artist->name }} ({{ $artist->specialty }})</option>
                                    @endforeach
                                </select>
                                <p id="artistBio" class="mt-2 text-sm text-gray-500 italic hidden"></p>
                            </div>

                            <div class="mb-6">
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Выберите дату*</label>
                                <select id="date" name="date" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                        onchange="loadAvailableTimes(this.value, document.getElementById('artist').value)"
                                        disabled>
                                    <option value="">-- Сначала выберите мастера --</option>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label for="time" class="block text-sm font-medium text-gray-700 mb-2">Выберите время*</label>
                                <select id="time" name="time" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                        disabled>
                                    <option value="">-- Сначала выберите дату --</option>
                                </select>
                            </div>

                            <div class="flex justify-between">
                                <button type="button" onclick="prevStep()" class="px-6 py-3 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <i class="fas fa-arrow-left mr-2"></i> Назад
                                </button>
                                <button type="button" onclick="nextStep()" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Далее: Подтверждение <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Подтверждение -->
                        <div id="step3" class="hidden">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Подтверждение записи</h2>
                            <div class="bg-gray-50 p-6 rounded-lg mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Детали записи</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Имя</p>
                                        <p id="confirmName" class="font-medium"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Телефон</p>
                                        <p id="confirmPhone" class="font-medium"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Описание татуировки</p>
                                        <p id="confirmDescription" class="font-medium"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Размер</p>
                                        <p id="confirmSize" class="font-medium"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Мастер</p>
                                        <p id="confirmArtist" class="font-medium"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Дата и время</p>
                                        <p id="confirmDateTime" class="font-medium"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="flex items-start">
                                    <input type="checkbox" name="consent" required 
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 mt-1">
                                    <span class="ml-2 text-sm text-gray-700">
                                        Я согласен на обработку персональных данных и подтверждаю, что мне больше 18 лет. 
                                        Я понимаю, что татуировка является перманентной процедурой.
                                    </span>
                                </label>
                            </div>

                            <div class="flex justify-between">
                                <button type="button" onclick="prevStep()" class="px-6 py-3 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <i class="fas fa-arrow-left mr-2"></i> Назад
                                </button>
                                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Подтвердить запись <i class="fas fa-check ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Боковая панель с информацией -->
                <div class="md:w-1/3 bg-gray-50 p-8 border-l border-gray-200">
                    <div class="sticky top-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Информация</h3>
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-medium text-gray-800">Подготовка к сеансу</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    Перед сеансом рекомендуется хорошо выспаться, не употреблять алкоголь за 24 часа и плотно поесть.
                                </p>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">Отмена записи</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    Если вам нужно отменить запись, пожалуйста, сообщите об этом как минимум за 48 часов до сеанса.
                                </p>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">Консультация</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    Перед первым сеансом мастер свяжется с вами для уточнения деталей и возможной коррекции эскиза.
                                </p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Контакты</h3>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600"><i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i> г. Москва, ул. Татуировочная, 13</p>
                                <p class="text-sm text-gray-600"><i class="fas fa-phone-alt mr-2 text-indigo-600"></i> +7 (999) 123-45-67</p>
                                <p class="text-sm text-gray-600"><i class="fas fa-envelope mr-2 text-indigo-600"></i> info@inkmaster.ru</p>
                                <p class="text-sm text-gray-600"><i class="fab fa-instagram mr-2 text-indigo-600"></i> @inkmaster_tattoo</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Управление шагами формы
        function nextStep() {
            const currentStep = document.querySelector('[id^="step"]:not(.hidden)');
            const nextStep = currentStep.nextElementSibling;
            
            // Валидация перед переходом
            if (currentStep.id === 'step1') {
                if (!validateStep1()) return;
                updateConfirmationData();
            } else if (currentStep.id === 'step2') {
                if (!validateStep2()) return;
                updateConfirmationData();
            }
            
            currentStep.classList.add('hidden');
            nextStep.classList.remove('hidden');
            
            // Обновление индикатора прогресса
            updateProgressIndicator(nextStep.id);
        }
        
        function prevStep() {
            const currentStep = document.querySelector('[id^="step"]:not(.hidden)');
            const prevStep = currentStep.previousElementSibling;
            
            currentStep.classList.add('hidden');
            prevStep.classList.remove('hidden');
            
            // Обновление индикатора прогресса
            updateProgressIndicator(prevStep.id);
        }
        
        function updateProgressIndicator(stepId) {
            const progressTexts = document.querySelectorAll('.flex.justify-between.mb-2 span');
            const progressBar = document.querySelector('.bg-indigo-600.h-2.5.rounded-full');
            
            progressTexts.forEach((span, index) => {
                if (stepId === 'step1' && index === 0) {
                    span.classList.remove('text-gray-500');
                    span.classList.add('text-indigo-600');
                } else if (stepId === 'step2' && index <= 1) {
                    span.classList.remove('text-gray-500');
                    span.classList.add('text-indigo-600');
                } else if (stepId === 'step3') {
                    span.classList.remove('text-gray-500');
                    span.classList.add('text-indigo-600');
                } else {
                    span.classList.remove('text-indigo-600');
                    span.classList.add('text-gray-500');
                }
            });
            
            if (stepId === 'step1') {
                progressBar.style.width = '33%';
            } else if (stepId === 'step2') {
                progressBar.style.width = '66%';
            } else if (stepId === 'step3') {
                progressBar.style.width = '100%';
            }
        }
        
        function validateStep1() {
            const name = document.getElementById('name').value;
            const phone = document.getElementById('phone').value;
            const description = document.getElementById('description').value;
            
            if (!name || !phone || !description) {
                alert('Пожалуйста, заполните все обязательные поля (имя, телефон и описание татуировки)');
                return false;
            }
            
            // Простая валидация телефона
            if (phone.length < 10) {
                alert('Пожалуйста, введите корректный номер телефона');
                return false;
            }
            
            return true;
        }
        
        function validateStep2() {
            const artist = document.getElementById('artist').value;
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            
            if (!artist || !date || !time) {
                alert('Пожалуйста, выберите мастера, дату и время сеанса');
                return false;
            }
            
            return true;
        }
        
        function updateConfirmationData() {
            document.getElementById('confirmName').textContent = document.getElementById('name').value;
            document.getElementById('confirmPhone').textContent = document.getElementById('phone').value;
            document.getElementById('confirmDescription').textContent = document.getElementById('description').value;
            
            const sizeValue = document.querySelector('input[name="size"]:checked').value;
            let sizeText = '';
            if (sizeValue === 'small') sizeText = 'Маленькая (<10см)';
            else if (sizeValue === 'medium') sizeText = 'Средняя (10-20см)';
            else sizeText = 'Большая (>20см)';
            document.getElementById('confirmSize').textContent = sizeText;
            
            const artistSelect = document.getElementById('artist');
            if (artistSelect.value) {
                document.getElementById('confirmArtist').textContent = artistSelect.options[artistSelect.selectedIndex].text;
            }
            
            const dateSelect = document.getElementById('date');
            const timeSelect = document.getElementById('time');
            if (dateSelect.value && timeSelect.value) {
                const date = new Date(dateSelect.value);
                const options = { day: 'numeric', month: 'long', year: 'numeric' };
                document.getElementById('confirmDateTime').textContent = 
                    `${date.toLocaleDateString('ru-RU', options)}, ${timeSelect.options[timeSelect.selectedIndex].text}`;
            }
        }
        
        // Загрузка доступных дат для выбранного мастера
        async function loadAvailableDates(artistId) {
            if (!artistId) {
                document.getElementById('date').innerHTML = '<option value="">-- Сначала выберите мастера --</option>';
                document.getElementById('date').disabled = true;
                document.getElementById('time').innerHTML = '<option value="">-- Сначала выберите дату --</option>';
                document.getElementById('time').disabled = true;
                document.getElementById('artistBio').classList.add('hidden');
                return;
            }
            
            // Показываем информацию о мастере
            const selectedOption = document.querySelector(`#artist option[value="${artistId}"]`);
            const specialty = selectedOption.getAttribute('data-specialty');
            document.getElementById('artistBio').textContent = `Специализация: ${specialty}`;
            document.getElementById('artistBio').classList.remove('hidden');
            
            // Загружаем доступные даты с сервера
            try {
                const response = await fetch(`/api/artists/${artistId}/available-dates`);
                const dates = await response.json();
                
                const dateSelect = document.getElementById('date');
                dateSelect.innerHTML = '<option value="">-- Выберите дату --</option>';
                
                dates.forEach(date => {
                    const option = document.createElement('option');
                    option.value = date.date;
                    option.textContent = new Date(date.date).toLocaleDateString('ru-RU', { 
                        weekday: 'long', 
                        day: 'numeric', 
                        month: 'long' 
                    });
                    dateSelect.appendChild(option);
                });
                
                dateSelect.disabled = false;
                document.getElementById('time').innerHTML = '<option value="">-- Сначала выберите дату --</option>';
                document.getElementById('time').disabled = true;
            } catch (error) {
                console.error('Ошибка при загрузке доступных дат:', error);
                alert('Произошла ошибка при загрузке доступных дат. Пожалуйста, попробуйте позже.');
            }
        }
        
        // Загрузка доступного времени для выбранной даты и мастера
        async function loadAvailableTimes(date, artistId) {
            if (!date || !artistId) {
                document.getElementById('time').innerHTML = '<option value="">-- Сначала выберите дату --</option>';
                document.getElementById('time').disabled = true;
                return;
            }
            
            try {
                const response = await fetch(`/api/artists/${artistId}/available-times?date=${date}`);
                const times = await response.json();
                
                const timeSelect = document.getElementById('time');
                timeSelect.innerHTML = '<option value="">-- Выберите время --</option>';
                
                times.forEach(time => {
                    const option = document.createElement('option');
                    option.value = time.time;
                    option.textContent = time.time;
                    timeSelect.appendChild(option);
                });
                
                timeSelect.disabled = false;
            } catch (error) {
                console.error('Ошибка при загрузке доступного времени:', error);
                alert('Произошла ошибка при загрузке доступного времени. Пожалуйста, попробуйте позже.');
            }
        }
        
        // Обработка отправки формы
        document.getElementById('tattooForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    // Успешная запись - показываем сообщение об успехе
                    alert('Ваша запись успешно оформлена! Мастер свяжется с вами для подтверждения.');
                    window.location.href = '/success'; // или сброс формы
                } else {
                    // Ошибка при записи
                    alert(result.message || 'Произошла ошибка при оформлении записи. Пожалуйста, попробуйте позже.');
                }
            } catch (error) {
                console.error('Ошибка при отправке формы:', error);
                alert('Произошла ошибка при отправке формы. Пожалуйста, попробуйте позже.');
            }
        });
    </script>
</body>
</html>
