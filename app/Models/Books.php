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
}
