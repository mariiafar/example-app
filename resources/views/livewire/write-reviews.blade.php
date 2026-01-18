<!-- Страница формы отзыва -->
<div class="bg-white p-8 rounded-xl shadow-sm max-w-3xl mx-auto mt-10">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Оставьте свой отзыв</h2>
   <form wire:submit.prevent="submitReview" enctype="multipart/form-data">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ваше имя</label>
            <input type="text" wire:model="user_name" class="w-full px-4 py-2 border rounded-lg">
            @error('user_name') <span class="text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" wire:model="email" class="w-full px-4 py-2 border rounded-lg">
            @error('email') <span class="text-red-600">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Ваш отзыв</label>
        <textarea wire:model="content" rows="4" class="w-full px-4 py-2 border rounded-lg"></textarea>
        @error('content') <span class="text-red-600">{{ $message }}</span> @enderror
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Фото (необязательно)</label>
        <input type="file" wire:model="photo" class="w-full">
        @error('photo') <span class="text-red-600">{{ $message }}</span> @enderror
    </div>

    <button type="submit" style="background-color: #3b82f6; color: white;" class="px-3 py-1 rounded">
        Отправить отзыв
    </button>
</form>
</div>
