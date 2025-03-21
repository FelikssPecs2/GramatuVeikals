<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\SaleController;


Route::resource('genres', GenreController::class);
Route::resource('authors', AuthorController::class);
Route::resource('books', BookController::class);
Route::resource('sales', SaleController::class);



Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');


// Authentication Routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');

// This route handles the logout functionality
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Registration Routes
Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest');

// Home Route (Authenticated Users)
Route::get('/home', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');

    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    Route::delete('/sales/delete', [SaleController::class, 'destroy'])->name('sales.destroy');


    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
