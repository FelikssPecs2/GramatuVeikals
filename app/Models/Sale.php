<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'book_id',
        'sale_date',  // Include sale_date here
        'quantity',
    ];

    // Relationships...



    // Define relationships if needed
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
