<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SalesAnalyticsController;

// Specific Routes
Route::get('/sales/export', [ExportController::class, 'exportSales'])->name('sales.export');
Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
Route::post('/books/update/{book}', [BookController::class, 'update'])->name('books.update');
Route::get('/books/{book}/genres', [BookController::class, 'getGenres'])->name('books.genres');
Route::delete('/sales/{sale}/delete', [SaleController::class, 'destroy'])->name('sales.destroy');
Route::get('/sales-analytics', [SalesAnalyticsController::class, 'index'])->name('sales-analytics');
Route::post('/sales-analytics/filter', [SalesAnalyticsController::class, 'filter'])->name('sales-analytics.filter');
Route::get('/get-list/{type}', [SalesAnalyticsController::class, 'getList'])->name('sales-analytics.get-list');

// Resource Routes
Route::resource('genres', GenreController::class);
Route::resource('authors', AuthorController::class);
Route::resource('books', BookController::class);
Route::resource('sales', SaleController::class);

// Home Route
Route::get('/home', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');

// Authentication Routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest');