<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;
    use HasFactory;

    // Add this relationship
    public function books()
    {
        return $this->hasMany(Book::class);
    }
    protected $fillable = ['name'];
}
