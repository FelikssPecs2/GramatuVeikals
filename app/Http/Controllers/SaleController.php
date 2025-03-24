<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Book;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    // Show all sales
    public function index()
    {
        $sales = Sale::with('book')->get();
        
        // Use '|' as the delimiter to avoid splitting the date
        $salesGrouped = $sales->groupBy(function($sale) {
            return $sale->book_id . '|' . $sale->sale_date;
        });
        
        $books = Book::all();
        return view('sales', compact('salesGrouped', 'books'));
    }

    // Sales Analytics (Most popular books, dates, genres, and authors)
    public function salesAnalytics()
    {
        // Sales Data (Grouped by Date)
        $salesData = Sale::join('books', 'sales.book_id', '=', 'books.id')
            ->selectRaw('sales.sale_date, SUM(sales.quantity) as total_quantity, SUM(books.price * sales.quantity) as total_sales_value')
            ->groupBy('sales.sale_date')
            ->orderBy('sales.sale_date')
            ->get();
    
        // Genre Sales Data (Grouped by Genre)
        $genreSalesData = Sale::join('books', 'sales.book_id', '=', 'books.id')
            ->join('genres', 'books.genre_id', '=', 'genres.id') // Join the genres table
            ->selectRaw('genres.name as genre_name, SUM(sales.quantity) as total_quantity, SUM(books.price * sales.quantity) as total_sales_value')
            ->groupBy('genres.name')
            ->orderByDesc('total_sales_value')
            ->get();
    
        // Author Sales Data (Grouped by Author)
        $authorSalesData = Sale::join('books', 'sales.book_id', '=', 'books.id')
            ->join('author_book', 'books.id', '=', 'author_book.book_id') // Join the pivot table
            ->join('authors', 'author_book.author_id', '=', 'authors.id') // Join the authors table
            ->selectRaw('authors.name as author_name, SUM(sales.quantity) as total_quantity, SUM(books.price * sales.quantity) as total_sales_value')
            ->groupBy('authors.name')
            ->orderByDesc('total_sales_value')
            ->get();
    
        return view('sales-analytics', compact('salesData', 'genreSalesData', 'authorSalesData'));
    }
    
    

    // Show the form to create a new sale
    public function create()
    {
        $books = Book::all();
        return view('sales.create', compact('books'));
    }

    // Store a new sale
    public function store(Request $request)
    {
        $request->validate([
            'book_id'   => 'required|exists:books,id',
            'sale_date' => 'required|date',
            'quantity'  => 'required|integer|min:1',
        ]);

        // Store the new sale record
        Sale::create([
            'book_id'   => $request->book_id,
            'sale_date' => $request->sale_date,
            'quantity'  => $request->quantity,
        ]);

        return redirect()->route('sales.index')->with('success', 'Grāmata veiksmīgi pievienota pārdošanai!');
    }

    // Update an existing sale
    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'book_id'   => 'required|exists:books,id',
            'sale_date' => 'required|date',
            'quantity'  => 'required|integer|min:1',
        ]);

        $sale->update([
            'book_id'   => $request->book_id,
            'sale_date' => $request->sale_date,
            'quantity'  => $request->quantity,
        ]);

        return redirect()->route('sales.index')->with('success', 'Pārdošana atjaunināta!');
    }

    // Show the form for editing an existing sale
    public function edit(Sale $sale)
    {
        $books = Book::all();
        return view('sales.edit', compact('sale', 'books'));
    }

    // Show details of a specific sale
    public function show($id)
    {
        $sale = Sale::with('book')->findOrFail($id);
        return view('sales.show', compact('sale'));
    }

    // Delete a sale record
    public function destroy(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Pārdošana dzēsta!');
    }
}
