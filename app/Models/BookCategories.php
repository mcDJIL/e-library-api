<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookCategories extends Model
{
    protected $fillable = [
        'book_id', 'category_id'
    ];
}
