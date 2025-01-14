<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookReviewsController;
use App\Http\Controllers\Api\BooksController;
use App\Http\Controllers\Api\BorrowRecordsController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/all-books', [BooksController::class, 'getAllBook']);
Route::get('/detail-book/{id}', [BooksController::class, 'detailBook']);
Route::get('/get-review/{id}', [BookReviewsController::class, 'getReview']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    //peminjam, superadmin & admin
    Route::post('/borrow-book/{id}', [BooksController::class, 'borrowBook']);
    Route::post('/add-favorite/{id}', [BooksController::class, 'addFavoriteBook']);
    Route::post('/remove-favorite/{id}', [BooksController::class, 'removeFavoriteBook']);
    Route::post('/send-review/{id}', [BookReviewsController::class, 'sendReview']);
    Route::get('/collection-books', [BooksController::class, 'getAllCollectionBook']);
    
    //superadmin & admin
    Route::get('peminjam', [UserController::class, 'getPeminjam']);
    Route::post('peminjam', [UserController::class, 'addPeminjam']);
    Route::get('peminjam/{id}', [UserController::class, 'showPeminjam']);
    Route::put('peminjam/{id}', [UserController::class, 'updatePeminjam']);
    Route::delete('peminjam/{id}', [UserController::class, 'destroyPeminjam']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('books', BooksController::class);
    Route::apiResource('categories', CategoriesController::class);
    Route::apiResource('borrow-records', BorrowRecordsController::class);
});