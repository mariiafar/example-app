<x-app-layout>
    <div class="flex flex-col lg:flex-row items-center justify-center gap-8 p-6 max-w-6xl mx-auto">
        {{-- Горизонтальная строка изображений с поляроидным эффектом --}}
        <div class="flex flex-nowrap overflow-x-auto gap-6 pb-4 scrollbar-hide">
            {{-- Изображение 1 --}}
            <div class="flex-shrink-0 bg-white p-3 pb-8 rounded shadow-lg transform rotate-1 hover:rotate-0 transition">
                <img src="{{ asset('storage/images/gallery3.jpg') }}" alt="Тату 1" 
                     class="w-40 h-40 object-cover rounded border border-gray-200" />
            </div>
            
            {{-- Изображение 2 --}}
            <div class="flex-shrink-0 bg-white p-3 pb-8 rounded shadow-lg transform -rotate-1 hover:rotate-0 transition">
                <img src="{{ asset('storage/images/gallery5.jpg') }}" alt="Тату 2" 
                     class="w-40 h-40 object-cover rounded border border-gray-200" />
            </div>
            
            {{-- Изображение 3 --}}
            <div class="flex-shrink-0 bg-white p-3 pb-8 rounded shadow-lg transform rotate-2 hover:rotate-0 transition">
                <img src="{{ asset('storage/images/gallery6.jpg') }}" alt="Тату 3" 
                     class="w-40 h-40 object-cover rounded border border-gray-200" />
            </div>
            
            {{-- Изображение 4 --}}
            <div class="flex-shrink-0 bg-white p-3 pb-8 rounded shadow-lg transform -rotate-2 hover:rotate-0 transition">
                <img src="{{ asset('storage/images/gallery7.jpg') }}" alt="Тату 4" 
                     class="w-40 h-40 object-cover rounded border border-gray-200" />
            </div>
        </div>

        
        <div class="flex-1 text-center lg:text-left">
            <p class="text-gray-600 dark:text-gray-300 text-lg mb-6">
                
            </p>
            
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow text-gray-800 dark:text-gray-100 text-lg mb-6">
                {{ __("Добро пожаловать в наш тату-салон. Здесь вы найдёте вдохновение для своего следующего шедевра!") }}
            </div>

        </div>
    </div>
</x-app-layout>