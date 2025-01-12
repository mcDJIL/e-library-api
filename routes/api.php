<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BooksController;
use App\Http\Controllers\Api\BorrowRecordsController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);

    //superadmin & admin
    Route::apiResource('users', UserController::class);
    Route::apiResource('books', BooksController::class);
    Route::apiResource('categories', CategoriesController::class);
    Route::apiResource('borrow-records', BorrowRecordsController::class);
});