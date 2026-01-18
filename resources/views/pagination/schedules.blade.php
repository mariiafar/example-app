@if ($paginator->hasPages())
    <nav class="flex justify-center mt-8" aria-label="Пагинация">
        <div class="flex items-center space-x-1">
            {{-- Кнопка "Назад" --}}
            <div>
                @if ($paginator->onFirstPage())
                    <span class="relative inline-flex items-center px-3 py-2 text-gray-400 bg-white border border-gray-300 rounded-lg cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span class="ml-1 hidden sm:inline">Назад</span>
                    </span>
                @else
                    <button
                        wire:click="previousPage"
                        wire:loading.attr="disabled"
                        class="relative inline-flex items-center px-3 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all duration-200 hover:shadow-sm active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span class="ml-1 hidden sm:inline">Назад</span>
                    </button>
                @endif
            </div>

            {{-- Номера страниц --}}
            <div class="hidden sm:flex items-center space-x-1">
                @foreach ($elements as $element)
                    {{-- "..." разделитель --}}
                    @if (is_string($element))
                        <span class="px-3 py-2 text-gray-500">...</span>
                    @endif

                    {{-- Массив страниц --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span 
                                    aria-current="page"
                                    class="relative z-10 inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 bg-blue-10000 border-blue-600 rounded-lg shadow-lg transform scale-105 transition-transform duration-200">
                                    {{ $page }}
                                </span>
                            @else
                                <button
                                    wire:click="gotoPage({{ $page }})"
                                    wire:loading.attr="disabled"
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all duration-200 hover:shadow-md active:scale-95">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Информация о страницах (мобильная версия) --}}
            <div class="sm:hidden flex items-center">
                <span class="px-3 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>
            </div>

            {{-- Кнопка "Вперёд" --}}
            <div>
                @if ($paginator->hasMorePages())
                    <button
                        wire:click="nextPage"
                        wire:loading.attr="disabled"
                        class="relative inline-flex items-center px-3 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all duration-200 hover:shadow-sm active:scale-95">
                        <span class="mr-1 hidden sm:inline">Вперёд</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @else
                    <span class="relative inline-flex items-center px-3 py-2 text-gray-400 bg-white border border-gray-300 rounded-lg cursor-not-allowed">
                        <span class="mr-1 hidden sm:inline">Вперёд</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
