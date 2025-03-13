<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    // Define which fields can be mass-assigned
    protected $fillable = ['title', 'price', 'author_id', 'genre_ids', 'age', 'pages', 'description'];

    // Relationships with Author and Genre models
    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }
}