<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowRecords extends Model
{
    protected $fillable = [
        'user_id', 'book_id', 'borrowed_at', 'returned_at', 'borrow_status'
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
