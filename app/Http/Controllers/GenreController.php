<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    
        public function __construct()
        {
            $this->middleware('admin')->only(['edit', 'update', 'destroy']);
        }
    
        // ... other methods ...
    
    
    // Show all genres
    public function index()
    {
        $genres = Genre::all();  // Fetch all genres from the database
        return view('genres', compact('genres'));  // Pass genres to the view
    }
    

    // Show the form to create a new genre
    public function create()
    {
        return view('genres.create');
    }

    // Store a new genre
    public function store(Request $request)
    {
        
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Genre::create($request->only('name'));

        return redirect()->route('genres.index'); 
    }

    // Show the form to edit a genre
    public function edit(Genre $genre)
    {
        return view('genres', compact('genre'));
    }

    // Update a genre
    public function update(Request $request, Genre $genre)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $genre->update($request->only('name'));

        return redirect()->route('genres.index');
    }

    // Delete a genre
    public function destroy(Genre $genre)
    {
        $genre->delete();
        return redirect()->route('genres.index'); 
    }
}