<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Author;
use App\Models\Genre;
use App\Models\Book;

class SalesAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Get lists for filters - only authors with books that have sales
        $authors = Author::whereHas('books.sales')->get();
        
        // Only genres that have books with sales
        $genres = Genre::whereHas('books.sales')->get();
        
        // Rest of your index method remains the same
        $salesData = $this->getSalesByDate($request);
        $genreData = $this->getSalesByGenre($request);
        $authorData = $this->getSalesByAuthor($request);
        
        return view('sales-analytics', compact('salesData', 'genreData', 'authorData', 'authors', 'genres'));
    }
    
    public function filter(Request $request)
    {
        try {
            $filterType = $request->input('filter_type', 'all');
            $specificFilter = $request->input('specific_filter');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
    
            $query = Sale::query();
    
            switch ($filterType) {
                case 'genre':
                    $query->whereHas('book.genres', function($q) use ($specificFilter) {
                        $q->where('genres.id', $specificFilter); // Explicitly specify table.column
                    });
                    break;
                    
                case 'author':
                    $query->whereHas('book.author', function($q) use ($specificFilter) {
                        $q->where('authors.id', $specificFilter); // Explicitly specify table.column
                    });
                    break;
                    
                case 'book':
                    $query->where('book_id', $specificFilter);
                    break;
            }
    
            // Apply date filters
            if ($startDate) {
                $query->where('sale_date', '>=', $startDate);
            }
            
            if ($endDate) {
                $query->where('sale_date', '<=', $endDate);
            }
    
            // Get results - using DATE() for consistent formatting
            $results = $query->selectRaw('DATE(sale_date) as label, SUM(quantity) as quantity')
                ->groupBy('label')  // Group by the formatted date
                ->orderBy('label')
                ->get();
    
            return response()->json([
                'success' => true,
                'results' => $results,
                'label' => $this->getFilterLabel($filterType, $specificFilter)
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering data: ' . $e->getMessage(),
                'results' => [],
                'label' => 'Error'
            ], 500);
        }
    }

private function getFilterLabel($type, $id)
{
    return match($type) {
        'genre' => Genre::find($id)->name ?? '',
        'author' => Author::find($id)->name ?? '',
        'book' => Book::find($id)->title ?? '',
        default => 'All Sales'
    };
}
        
     
    
    public function getList($type)
    {
        $data = match($type) {
            'genre-list' => Genre::has('books.sales')->select('id', 'name')->get(),
            'author-list' => Author::has('books.sales')->select('id', 'name')->get(),
            'book-list' => Book::has('sales')->select('id', 'title as name')->get(),
            default => []
        };
        
        return response()->json($data);
    }
    
    // ... [keep all your other methods exactly as they were] ...
    
    private function getSalesByGenre(Request $request)
    {
        return Sale::join('books', 'sales.book_id', '=', 'books.id')
            ->join('book_genre', 'books.id', '=', 'book_genre.book_id')
            ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
            ->selectRaw('genres.id, genres.name as label, SUM(sales.quantity) as quantity')
            ->when($request->has('start_date'), function($query) use ($request) {
                $query->where('sale_date', '>=', $request->start_date);
            })
            ->when($request->has('end_date'), function($query) use ($request) {
                $query->where('sale_date', '<=', $request->end_date);
            })
            ->groupBy('genres.id', 'genres.name')
            ->get();
    }
    
    
    private function getSalesByAuthor(Request $request)
    {
        return Sale::join('books', 'sales.book_id', '=', 'books.id')
            ->join('authors', 'books.author_id', '=', 'authors.id')
            ->selectRaw('authors.name as label, SUM(sales.quantity) as quantity')
            ->when($request->has('start_date'), function($query) use ($request) {
                $query->where('sale_date', '>=', $request->start_date);
            })
            ->when($request->has('end_date'), function($query) use ($request) {
                $query->where('sale_date', '<=', $request->end_date);
            })
            ->groupBy('authors.id', 'authors.name')
            ->get();
    }

    // NEW METHOD: Get sales data for multiple filters combined
    public function getCombinedSales(Request $request)
    {
        $query = Sale::query();
        
        if ($request->has('genres')) {
            $query->whereHas('book.genres', function($q) use ($request) {
                $q->whereIn('id', $request->genres);
            });
        }
        
        if ($request->has('authors')) {
            $query->whereHas('book.author', function($q) use ($request) {
                $q->whereIn('id', $request->authors);
            });
        }
        
        if ($request->has('start_date')) {
            $query->where('sale_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('sale_date', '<=', $request->end_date);
        }
        
        return $query->selectRaw('DATE(sale_date) as date, SUM(quantity) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getSalesByDate(Request $request)
{
    return Sale::query()
        ->when($request->has('start_date'), function($query) use ($request) {
            $query->where('sale_date', '>=', $request->start_date);
        })
        ->when($request->has('end_date'), function($query) use ($request) {
            $query->where('sale_date', '<=', $request->end_date);
        })
        ->selectRaw('sale_date as label, SUM(quantity) as quantity')
        ->groupBy('sale_date')
        ->orderBy('sale_date')
        ->get();
}
    // NEW METHOD: Get top selling items
    public function getTopSellers(Request $request)
    {
        $limit = $request->input('limit', 10);
        $type = $request->input('type', 'books');
        
        switch ($type) {
            case 'books':
                return Sale::join('books', 'sales.book_id', '=', 'books.id')
                    ->selectRaw('books.title as name, SUM(sales.quantity) as total')
                    ->groupBy('books.id', 'books.title')
                    ->orderByDesc('total')
                    ->limit($limit)
                    ->get();
                
            case 'authors':
                return Sale::join('books', 'sales.book_id', '=', 'books.id')
                    ->join('authors', 'books.author_id', '=', 'authors.id')
                    ->selectRaw('authors.name, SUM(sales.quantity) as total')
                    ->groupBy('authors.id', 'authors.name')
                    ->orderByDesc('total')
                    ->limit($limit)
                    ->get();
                    
            case 'genres':
                return Sale::join('books', 'sales.book_id', '=', 'books.id')
                    ->join('book_genre', 'books.id', '=', 'book_genre.book_id')
                    ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
                    ->selectRaw('genres.name, SUM(sales.quantity) as total')
                    ->groupBy('genres.id', 'genres.name')
                    ->orderByDesc('total')
                    ->limit($limit)
                    ->get();
        }
    }
}