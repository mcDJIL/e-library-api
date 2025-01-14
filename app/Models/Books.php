<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    protected $fillable = [
        'title', 'description', 'author', 'publisher', 'published_at', 'book_cover'
    ];

    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'book_categories', 'book_id', 'category_id');
    }

    public function bookReviews()
    {
        return $this->hasMany(BookReviews::class, 'book_id');
    }

    public function bookCollections()
    {
        return $this->hasMany(BookCollections::class, 'book_id');
    }

    public function borrowRecords()
    {
        return $this->hasMany(BorrowRecords::class, 'book_id');
    }

    public function getAvailabilityStatusAttribute()
    {
        // Cek apakah ada borrow record yang statusnya "borrowed"
        $isBorrowed = $this->borrowRecords()->where('borrow_status', 'borrowed')->exists();
        return $isBorrowed ? 'Buku Sedang Dipinjam' : 'Buku Tersedia';
    }
}
