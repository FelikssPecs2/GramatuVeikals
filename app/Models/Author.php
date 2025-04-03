<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    // Add sales relationship through books
    public function sales()
    {
        return $this->hasManyThrough(
            Sale::class,
            Book::class,
            'author_id',   // Foreign key on books table
            'book_id',     // Foreign key on sales table
            'id',          // Local key on authors table
            'id'           // Local key on books table
        );
    }
}