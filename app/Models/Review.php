<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_name',
        'review_date',
        'content',
        'photo',
    ];

    protected $dates = [
        'review_date',
    ];
}
