<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookReviews;
use App\Models\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookReviewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $book_reviews = BookReviews::with(['user', 'book'])->get();

        return response()->json([
            'message' => 'Daftar ulasan buku berhasil diambil.',
            'data' => $book_reviews
        ]);
    }

    public function getReview($id)
    {
        $book_reviews = BookReviews::with(['user', 'book'])
        ->where('book_id', $id)
        ->get();

        return response()->json([
            'message' => 'Daftar ulasan buku berhasil diambil.',
            'data' => $book_reviews
        ]);
    }

    public function sendReview(Request $request, $id)
    {
        $user = Auth::user();

        $book = Books::find($id);

        $validation = Validator::make($request->all(), [
            'review' => 'required',
            'rating' => 'required',
        ]);

        if ($validation->fails())
        {
            return response()->json([
                'message' => Str::ucfirst($validation->errors()->first())
            ], 422);
        }

        $send = BookReviews::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'review' => $request->review,
            'rating' => $request->rating,
        ]);

        if ($send)
        {
            return response()->json([
                'message' => 'Berhasil mengirim ulasan'
            ], 200);
        }
    }
}
