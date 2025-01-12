<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BooksController extends Controller
{
    // Get all books
    public function index()
    {
        $books = Books::with('categories')->get();

        return response()->json([
            'message' => 'Daftar buku berhasil diambil.',
            'data' => $books
        ]);
    }

    // Store a new book
    public function store(Request $request)
    {
        try {
            //code...
            Log::info('Received request:', $request->all());
            Log::info('Files:', $request->allFiles());

            $validation = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'author' => 'required|string|max:255',
                'publisher' => 'required|string|max:255',
                'published_at' => 'nullable|integer',
                'book_cover' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'categories' => 'required|array',
                'categories.*' => 'exists:categories,id',
            ]);

            if ($validation->fails())
            {
                return response()->json([
                    'message' => Str::ucfirst($validation->errors()->first()),
                ], 422);
            }

            $bookCover = $request->file('book_cover');
            $coverPath = 'uploads/books/' . time() . '_' . $bookCover->getClientOriginalName();
            $bookCover->move(public_path('uploads/books'), $coverPath);

            $book = Books::create([
                'title' => $request->title,
                'description' => $request->description,
                'author' => $request->author,
                'publisher' => $request->publisher,
                'published_at' => $request->published_at,
                'book_cover' => $coverPath,
            ]);

            $book->categories()->attach($request->categories);

            return response()->json([
                'message' => 'Buku berhasil ditambahkan.',
                'data' => $book->load('categories')
            ], 201);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $th,
            ], 401);
        }
    }

    // Show a single book
    public function show($id)
    {
        $book = Books::with('categories')->find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Buku tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail buku berhasil diambil.',
            'data' => $book
        ]);
    }

    // Update a book
    public function update(Request $request, $id)
    {
        try {
            Log::info('Received request:', $request->all());
            Log::info('Files:', $request->allFiles());

            //code...
            $book = Books::find($id);

            if (!$book) {
                return response()->json([
                    'message' => 'Buku tidak ditemukan.'
                ], 404);
            }

            $validation = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'author' => 'required|string|max:255',
                'publisher' => 'required|string|max:255',
                'published_at' => 'nullable|integer',
                'book_cover' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'categories' => 'required|array',
                'categories.*' => 'exists:categories,id',
            ]);

            if ($validation->fails())
                {
                    return response()->json([
                        'message' => Str::ucfirst($validation->errors()->first()),
                    ], 422);
                }

            // Handle book cover
            if ($request->hasFile('book_cover')) {
                if (File::exists(public_path($book->book_cover))) {
                    File::delete(public_path($book->book_cover));
                }

                $bookCover = $request->file('book_cover');
                $coverPath = 'uploads/books/' . time() . '_' . $bookCover->getClientOriginalName();
                $bookCover->move(public_path('uploads/books'), $coverPath);

                $book->book_cover = $coverPath;
            }

            $book->update([
                'title' => $request->title,
                'description' => $request->description,
                'author' => $request->author,
                'publisher' => $request->publisher,
                'published_at' => $request->published_at,
            ]);

            // Update categories
            $book->categories()->sync($request->categories);

            return response()->json([
                'message' => 'Buku berhasil diperbarui.',
                'data' => $book->load('categories')
            ]);
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $th,
            ], 401);
        }        
    }

    // Delete a book
    public function destroy($id)
    {
        $book = Books::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Buku tidak ditemukan.'
            ], 404);
        }

        if (File::exists(public_path($book->book_cover))) {
            File::delete(public_path($book->book_cover));
        }

        $book->delete();

        return response()->json([
            'message' => 'Buku berhasil dihapus.'
        ]);
    }
}
