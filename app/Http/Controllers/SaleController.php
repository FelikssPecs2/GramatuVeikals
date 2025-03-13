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
    $sales = Sale::selectRaw('book_id, sale_date, SUM(quantity) as total_quantity')
        ->groupBy('book_id', 'sale_date')
        ->with('book')
        ->get();

    $books = Book::all();
    
    return view('sales', compact('sales', 'books'));
}


    // Show the form to create a new sale
    public function create()
    {
        $books = Book::all();
        return view('sales.create', compact('books'));
    }

    public function destroy(Request $request)
    {
        Sale::where('book_id', $request->book_id)
            ->where('sale_date', $request->sale_date)
            ->delete();
    
        return redirect()->route('sales.index')->with('success', 'Pārdošana dzēsta!');
    }
    // Store a new sale
    public function store(Request $request)
    {
        $request->validate([
            'book_id'   => 'required|exists:books,id',
            'sale_date' => 'required|date',
            'quantity'  => 'required|integer|min:1',
        ]);

        Sale::create([
            'book_id'   => $request->book_id,
            'sale_date' => $request->sale_date,
            'quantity'  => $request->quantity,
        ]);

        return redirect()->route('sales.index')->with('success', 'Grāmata veiksmīgi pievienota pārdošanai!');
    }

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
        $sale = Sale::with('book')->find($sale->id); // Eager load the 'book' relationship
        return view('sales', compact('sale', 'books'));
    }
}