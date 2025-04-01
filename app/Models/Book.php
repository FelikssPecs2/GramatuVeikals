<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'price', 
        'author_id', 
        'age', 
        'pages', 
        'description'
    ];

    // Relationships
    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }



    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_book');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}