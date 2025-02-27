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
Route::get('/popular-books', [BooksController::class, 'getPopularBook']);
Route::get('/detail-book/{id}', [BooksController::class, 'detailBook']);
Route::get('/get-review/{id}', [BookReviewsController::class, 'getReview']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    //peminjam
    Route::middleware('role:peminjam')->group(function () {
        Route::post('/borrow-book/{id}', [BooksController::class, 'borrowBook']);
        Route::post('/add-favorite/{id}', [BooksController::class, 'addFavoriteBook']);
        Route::post('/remove-favorite/{id}', [BooksController::class, 'removeFavoriteBook']);
        Route::post('/send-review/{id}', [BookReviewsController::class, 'sendReview']);
        Route::get('/collection-books', [BooksController::class, 'getAllCollectionBook']);
        Route::get('/borrow-history', [BorrowRecordsController::class, 'getBorrowHistory']);
    });
    
    //superadmin & admin
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::get('peminjam', [UserController::class, 'getPeminjam']);
        Route::post('peminjam', [UserController::class, 'addPeminjam']);
        Route::get('peminjam/{id}', [UserController::class, 'showPeminjam']);
        Route::put('peminjam/{id}', [UserController::class, 'updatePeminjam']);
        Route::delete('peminjam/{id}', [UserController::class, 'destroyPeminjam']);
        Route::apiResource('books', BooksController::class);
        Route::apiResource('categories', CategoriesController::class);
        Route::apiResource('book-reviews', BookReviewsController::class);
        Route::apiResource('borrow-records', BorrowRecordsController::class);
        Route::put('borrow_verification/{id}', [BorrowRecordsController::class, 'borrow_verification']);
        Route::get('report', [BorrowRecordsController::class, 'report']);
    });

    //superadmin
    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});