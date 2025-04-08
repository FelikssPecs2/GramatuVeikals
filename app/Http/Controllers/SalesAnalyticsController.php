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
        
        return view('sales-analytics', compact(
            'genres', 
            'authors', 
            'books'
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

            // Handle "all" filter case
            if ($filterType === 'all' || empty($specificFilters)) {
                $query = Sale::query();
                
                if ($startDate) {
                    $query->where('sale_date', '>=', $startDate);
                }
                
                if ($endDate) {
                    $query->where('sale_date', '<=', $endDate);
                }

                $data = $query->selectRaw('DATE(sale_date) as date, SUM(quantity) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->pluck('total', 'date')
                    ->toArray();

                $results[] = [
                    'label' => 'Visi pārdošanas',
                    'data' => $data
                ];
            } else {
                // Handle specific filters
                foreach ((array)$specificFilters as $filterId) {
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
                            
                        default:
                            $label = 'Unknown Filter';
                            break;
                    }

                    // Date filters
                    if ($startDate) {
                        $query->where('sale_date', '>=', $startDate);
                    }
                    
                    if ($endDate) {
                        $query->where('sale_date', '<=', $endDate);
                    }

                    // Get data
                    $data = $query->selectRaw('DATE(sale_date) as date, SUM(quantity) as total')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get()
                        ->pluck('total', 'date')
                        ->toArray();

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
            Log::error('Sales filter error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Kļūda filtrējot datus: ' . $e->getMessage()
            ], 500);
        }
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