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
        // Iegūt pārdošanas datus ar filtriem
        $sales = Sale::with(['book.author', 'book.genres'])
            ->when($request->has('author'), function ($query) use ($request) {
                $query->whereHas('book.author', function ($q) use ($request) {
                    $q->where('id', $request->author);
                });
            })
            ->when($request->has('genre'), function ($query) use ($request) {
                $query->whereHas('book.genres', function ($q) use ($request) {
                    $q->where('id', $request->genre);
                });
            })
            ->when($request->has('start_date'), function ($query) use ($request) {
                $query->where('sale_date', '>=', $request->start_date);
            })
            ->when($request->has('end_date'), function ($query) use ($request) {
                $query->where('sale_date', '<=', $request->end_date);
            })
            ->get();
    
        // Iegūt autorus un žanrus filtru izvēlnei
        $authors = Author::all();
        $genres = Genre::all();
    
        return view('sales-analytics', compact('sales', 'authors', 'genres'));
    }

    private function fetchApiData()
    {
        // Piemērs: Iegūt datus no API
        $response = Http::get('https://api.example.com/data', [
            'api_key' => 'your_api_key_here',
        ]);

        return $response->json();
    }
}