<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Genre;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    // Display all books
    public function index(Request $request)
    {
        // Get all authors and genres for the dropdowns
        $authors = Author::all();
        $genres = Genre::all();
    
        // Start the query
        $query = Book::with('author', 'genres');
    
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . str_replace(' ', '%', $request->search) . '%');
        }
    
        // Filter by author
        if ($request->filled('author')) {
            $query->where('author_id', $request->author);
        }
    
        // Filter by genre
        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre);
            });
        }
    
        // Paginate the filtered results (10 books per page)
        $books = $query->paginate(10);
    
        // Pass data to the view
        return view('books', compact('books', 'authors', 'genres'));
    }
    // Show form for creating a new book
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'author_id' => 'required|exists:authors,id',
            'genre_ids' => 'required|array',
            'genre_ids.*' => 'exists:genres,id',
            'price' => 'required|numeric',
            'age' => 'nullable|integer',
            'pages' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);
    
        // Create the book
        $book = Book::create($request->only(['title', 'author_id', 'price', 'age', 'pages', 'description']));
    
        // Attach genres
        $book->genres()->sync($request->genre_ids);
    
        // Redirect back to the same page with search/filter parameters
        return redirect()->route('books.index', [
            'page' => $request->page, // Preserve the current page number
            'search' => $request->search, // Preserve the search parameter
            'author' => $request->author, // Preserve the author filter
            'genre' => $request->genre, // Preserve the genre filter
        ]);
    }
    
public function getGenres(Book $book)
{
    return response()->json($book->genres);
}
    // Show form for editing a book
    public function edit($id)
    {
        $book = Book::findOrFail($id);
        $authors = Author::all();
        $genres = Genre::all();
        $books = Book::with('author', 'genres')->get(); // Fetch all books for listing

        // Return the same view (books.blade.php) with the $book variable
        return view('books', compact('book', 'books', 'authors', 'genres'));
    }

    // Update an existing book
public function update(Request $request, Book $book)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'author_id' => 'required|exists:authors,id',
        'genre_ids' => 'required|array',
        'genre_ids.*' => 'exists:genres,id',
        'price' => 'required|numeric|min:0',
        'age' => 'nullable|integer',
        'pages' => 'nullable|integer',
        'description' => 'nullable|string',
    ]);

    // Update the book fields
    $book->update([
        'title' => $request->title,
        'author_id' => $request->author_id,
        'price' => $request->price,
        'age' => $request->age,  // Update age
        'pages' => $request->pages,  // Update pages
        'description' => $request->description,  // Update description
    ]);

    // Sync genres
    $book->genres()->sync($request->genre_ids);

    return redirect()->route('books.index', request()->query());
}

    // Delete a book
    public function destroy(Book $book)
    {
        $book->genres()->detach(); // Detach genres before deleting the book
        $book->delete();

        return redirect()->route('books.index');
    }

    
}