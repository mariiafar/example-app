<div class="p-6 space-y-6 bg-gray-50 dark:bg-gray-900 rounded-xl shadow-md">
 
    

    
    @if (session()->has('success'))
        <div class="text-green-600">{{ session('success') }}</div>
    @endif

  
    @if(auth()->user()->role === 'client')
        <div class="text-left">
            <a href="{{ route('write-reviews') }}" style="background-color: #3b82f6; color: white;" class="px-3 py-1 rounded">
                Оставить отзыв
            </a>
        </div>
    @endif

    
    <div class="mb-6 space-y-4">
        <div class="flex flex-col sm:flex-row gap-4">
            
            <div class="flex gap-2 flex-grow">
                <input wire:model="search" placeholder="Поиск по имени..." class="w-full p-2 border rounded"/>
                <button wire:click="applySearch" class="px-4 py-2 border rounded bg-gray-200">Найти</button>
                @if($appliedSearch)
                    <button wire:click="resetSearch" class="px-4 py-2 border rounded bg-red-200">Сбросить</button>
                @endif
            </div>
          
            <select wire:change="changeSortDirection($event.target.value)" class="p-2 border rounded">
                <option value="asc">Сначала старые</option>
                <option value="desc">Сначала новые</option>
            </select>
        </div>
    </div>

    
    <div class="space-y-4">
        @forelse ($reviews as $review)
            <div class="border border-gray-300 rounded p-4 space-y-2 bg-white dark:bg-gray-800">
                <div class="flex justify-between">
                    <div>
                        <strong>{{ $review->user_name }}</strong>
                        <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($review->review_date)->format('d.m.Y') }}</span>
                    </div>
                    @if(auth()->user()->role === 'admin')
                        <button wire:click="deleteReview({{ $review->id }})" class="text-red-600 hover:underline">Удалить</button>
                    @endif
                </div>
                <div>{{ $review->content }}</div>
                @if ($review->photo)
                    <img src="{{ Storage::url($review->photo) }}" class="w-48 h-48 object-cover rounded">
                @endif
            </div>
        @empty
            <div class="text-gray-600">Отзывов пока нет.</div>
        @endforelse
    </div>
</div>



