<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Author;
use App\Models\Genre;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesAnalyticsController extends Controller
{
    public function index()
    {
        // Get filter lists
        $genres = Genre::orderBy('name')->get(['id', 'name']);
        $authors = Author::orderBy('name')->get(['id', 'name']);
        $books = Book::with('author')->orderBy('title')->get(['id', 'title', 'author_id']);
        
        // Get initial sales data for the chart
        $salesData = Sale::selectRaw('DATE(sale_date) as label, SUM(quantity) as quantity')
            ->groupBy('label')
            ->orderBy('label')
            ->get();
        
        // Get genre sales data - FIXED VERSION
        $genreData = DB::table('genres')
            ->select(
                'genres.id',
                'genres.name',
                DB::raw('COALESCE(SUM(sales.quantity), 0) as total_quantity')
            )
            ->leftJoin('book_genre', 'genres.id', '=', 'book_genre.genre_id')
            ->leftJoin('books', 'book_genre.book_id', '=', 'books.id')
            ->leftJoin('sales', 'books.id', '=', 'sales.book_id')
            ->groupBy('genres.id', 'genres.name')
            ->orderBy('genres.name')
            ->get();
        
        // Get author sales data
        $authorData = Author::withCount(['sales as total_quantity' => function($query) {
                $query->select(DB::raw('SUM(quantity)'));
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'total_quantity']);
        
        return view('sales-analytics', compact(
            'genres', 
            'authors', 
            'books', 
            'salesData',
            'genreData',
            'authorData'
        ));
    }

    public function filter(Request $request)
{
    try {
        $filterType = $request->input('filter_type', 'all');
        $specificFilters = $request->input('specific_filters', []);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $results = [];
        $labels = [];

        // Ja ir atlasīti vairāki filtri
        if (is_array($specificFilters)) {  // Fixed: Added closing parenthesis
            foreach ($specificFilters as $filterId) {
                $query = Sale::query();

                switch ($filterType) {
                    case 'genre':
                        $query->whereHas('book.genres', function($q) use ($filterId) {
                            $q->where('genres.id', $filterId);
                        });
                        $label = Genre::find($filterId)->name ?? 'Unknown Genre';
                        break;
                        
                    case 'author':
                        $query->whereHas('book.author', function($q) use ($filterId) {
                            $q->where('authors.id', $filterId);
                        });
                        $label = Author::find($filterId)->name ?? 'Unknown Author';
                        break;
                        
                    case 'book':
                        $query->where('book_id', $filterId);
                        $label = Book::find($filterId)->title ?? 'Unknown Book';
                        break;
                }

                // Pievieno datumu filtrus
                if ($startDate) {
                    $query->where('sale_date', '>=', $startDate);
                }
                
                if ($endDate) {
                    $query->where('sale_date', '<=', $endDate);
                }

                // Iegūst datus
                $data = $query->selectRaw('DATE(sale_date) as date, SUM(quantity) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->pluck('total', 'date');

                $results[] = [
                    'label' => $label,
                    'data' => $data
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Kļūda filtrējot datus: ' . $e->getMessage()
        ], 500);
    }
}

    private function getFilterLabel($type, $id)
    {
        if (empty($id)) return 'All Sales';
        
        return match($type) {
            'genre' => Genre::find($id)->name ?? 'Unknown Genre',
            'author' => Author::find($id)->name ?? 'Unknown Author',
            'book' => Book::find($id)->title ?? 'Unknown Book',
            default => 'All Sales'
        };
    }

    public function getList($type)
    {
        try {
            switch ($type) {
                case 'genre-list':
                    $data = Genre::orderBy('name')->get(['id', 'name']);
                    break;
                case 'author-list':
                    $data = Author::orderBy('name')->get(['id', 'name']);
                    break;
                case 'book-list':
                    $data = Book::orderBy('title')->get(['id', 'title as name']);
                    break;
                default:
                    $data = collect();
                    break;
            }

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}