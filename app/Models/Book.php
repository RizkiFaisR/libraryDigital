<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'isbn',
        'author',
        'year',
        'category_id',
        'publisher_id',
        'copies',
        'description',
        'cover',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    /**
     * Return a public URL for the cover image if present.
     */
    public function getCoverUrlAttribute()
    {
        if (!empty($this->cover)) {
            return asset('storage/' . $this->cover);
        }

        return null;
    }
}
