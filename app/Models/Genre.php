<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_genre');
    }

    // Updated sales relationship
    public function sales()
    {
        return $this->hasManyThrough(
            Sale::class,           // The target model we want to access
            Book::class,            // The intermediate model
            'id',                   // Foreign key on the intermediate model (books)
            'book_id',              // Foreign key on the target model (sales)
            'id',                   // Local key on this model (genres)
            'id'                    // Local key on intermediate model (books)
        )->join('book_genre', 'books.id', '=', 'book_genre.book_id')
         ->whereColumn('book_genre.genre_id', 'genres.id');
    }
}