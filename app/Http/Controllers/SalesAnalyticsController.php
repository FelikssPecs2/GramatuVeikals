<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Sale;
use App\Models\Author;
use App\Models\Genre;

class SalesAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Filtered sales data (keep your existing code)
        $sales = Sale::with(['book.author', 'book.genres'])
            ->when($request->has('author'), function($query) use ($request) {
                $query->whereHas('book.author', function($q) use ($request) {
                    $q->where('id', $request->author);
                });
            })
            ->when($request->has('genre'), function($query) use ($request) {
                $query->whereHas('book.genres', function($q) use ($request) {
                    $q->where('id', $request->genre);
                });
            })
            ->when($request->has('start_date'), function($query) use ($request) {
                $query->where('sale_date', '>=', $request->start_date);
            })
            ->when($request->has('end_date'), function($query) use ($request) {
                $query->where('sale_date', '<=', $request->end_date);
            })
            ->get();
        
        // Sales by date data (MISSING IN YOUR CODE)
        $salesData = Sale::selectRaw('sale_date, SUM(quantity) as total_quantity')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();
    
        // Genre data
        $genreData = Sale::join('books', 'sales.book_id', '=', 'books.id')
        ->join('book_genre', 'books.id', '=', 'book_genre.book_id')
        ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
        ->selectRaw('genres.name as label, SUM(sales.quantity) as quantity')
        ->groupBy('genres.name')
        ->get();
    
        // Author data
        $authorData = Sale::join('books', 'sales.book_id', '=', 'books.id')
        ->join('authors', 'books.author_id', '=', 'authors.id')
        ->selectRaw('authors.name as label, SUM(sales.quantity) as quantity')
        ->groupBy('authors.id', 'authors.name')
        ->get();
    
        $authors = Author::all();
        $genres = Genre::all();
        
        return view('sales-analytics', compact('sales', 'authors', 'genres', 'salesData', 'genreData', 'authorData'));    }
    //ja ir vajadzigs iegut datus no apii
    private function fetchApiData()
    {
        
        $response = Http::get('https://api.example.com/data', [
            'api_key' => 'your_api_key_here',
        ]);

        return $response->json();
    }
}