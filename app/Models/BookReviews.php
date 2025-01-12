<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookReviews extends Model
{
    protected $fillable = [
        'user_id', 'book_id', 'review', 'rating'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function book()
    {
        return $this->belongsTo(Books::class, 'book_id');
    }
}
