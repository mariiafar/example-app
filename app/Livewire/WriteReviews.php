<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Review;

class WriteReviews extends Component
{
    use WithFileUploads;

    public $user_name;
    public $email;
    public $content;
    public $photo;

    public function submitReview()
    {
        $validated = $this->validate([
            'user_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'content' => 'required|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($this->photo) {
            $validated['photo'] = $this->photo->store('reviews', 'public');
        }

        $validated['review_date'] = now();

        Review::create($validated);

        return redirect()->route('reviews')->with('success', 'Спасибо за ваш отзыв!');
    }

    public function render()
    {
        return view('livewire.write-reviews');
    }
}
