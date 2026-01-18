<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;

class Reviews extends Component
{
    public $reviews;
    public $search = '';
    public $sortDirection = 'desc';
    public $appliedSearch = '';

    public function mount()
    {
        $this->loadReviews();
    }

    public function loadReviews()
    {
        $this->reviews = Review::when($this->appliedSearch, function ($query) {
                $query->where('user_id', 'like', '%' . $this->appliedSearch . '%');
            })
            ->orderBy('review_date', $this->sortDirection)
            ->get();
    }

    public function applySearch()
    {
        $this->appliedSearch = $this->search;
        $this->loadReviews();
    }

    public function resetSearch()
    {
        $this->search = '';
        $this->appliedSearch = '';
        $this->loadReviews();
    }

    public function changeSortDirection($direction)
    {
        $this->sortDirection = $direction;
        $this->loadReviews();
    }

    public function deleteReview($id)
    {
        $review = Review::findOrFail($id);

        if ($review->photo && Storage::disk('public')->exists($review->photo)) {
            Storage::disk('public')->delete($review->photo);
        }

        $review->delete();

        $this->loadReviews();

        session()->flash('success', 'Отзыв успешно удалён!');
    }

    public function render()
    {
        return view('livewire.reviews');
    }
}