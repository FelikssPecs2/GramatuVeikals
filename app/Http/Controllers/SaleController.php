<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Book;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('book')->get();
        
        $salesGrouped = $sales->groupBy(function($sale) {
            return $sale->book_id . '|' . $sale->sale_date;
        });
        
        $books = Book::all();
        return view('sales', compact('salesGrouped', 'books'));
    }

    
    

    public function create()
    {
        $books = Book::all();
        return view('sales.create', compact('books'));
    }

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

    public function edit(Sale $sale)
    {
        $books = Book::all();
        return view('sales.edit', compact('sale', 'books'));
    }

    public function show($id)
    {
        $sale = Sale::with('book')->findOrFail($id);
        return view('sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Pārdošana dzēsta!');
    }
}