<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    // Show all authors
    public function index()
    {
        $authors = Author::all(); // Get all authors
        return view('authors', compact('authors')); // Pass authors to the view
    }
    
    // Show the form to create a new author
    public function create()
    {
        return view('authors.create');
    }

    // Store a new author
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Author::create($request->only('name'));


        return redirect()->route('authors.index');
    }

    // Show the form to edit an author
// Show the form to edit an author
public function edit(Author $author)
{
    $authors = Author::all(); // Fetch all authors

    return view('authors', compact('authors', 'author'));
}


    // Update an author
    public function update(Request $request, Author $author)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        $author->update($request->only('name'));
    
        return redirect()->route('authors.index');
    }

    // Delete an author
    public function destroy(Author $author)
    {
        $author->delete();
        return redirect()->route('authors.index');
    }
}
