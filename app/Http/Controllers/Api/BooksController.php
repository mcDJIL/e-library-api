<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookCollections;
use App\Models\Books;
use App\Models\BorrowRecords;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BooksController extends Controller
{
    // Get all books
    public function index()
    {
        $books = Books::with('categories')->orderBy('title', 'asc')->get();

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

    public function getAllBook(Request $request)
    {
        $name = $request->query('name');
        $user = User::where('name', $name)->first();
        
        // Query dasar untuk mengambil semua buku
        $query = Books::with('categories')
            ->with('borrowRecords');
        
        // Jika pengguna login, tambahkan relasi bookCollections
        if ($user) {
            $query->with(['bookCollections' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }]);
        }
        
        // Eksekusi query
        $books = $query->get();
        
        return response()->json([
            'data' => $books->map(function ($book) use ($user) {
                
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'description' => $book->description,
                    'author' => $book->author,
                    'publisher' => $book->publisher,
                    'published_at' => $book->published_at,
                    'book_cover' => $book->book_cover,
                    'availability_status' => $book->availability_status,
                    'favorite_book' => $user ? !$book->bookCollections->isEmpty() : false,
                ];
            }),
        ]);
    }

    public function getAllCollectionBook()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
                'status' => 401
            ], 401);
        }

        // Query untuk mengambil hanya buku yang difavoritkan
        $query = Books::with(['categories', 'bookReviews'])
            ->withAvg('bookReviews as rating_average', 'rating')
            ->withCount('bookReviews as total_reviews')
            ->whereHas('bookCollections', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

        // Eksekusi query
        $books = $query->get();
        
        return response()->json([
            'status' => 200,
            'total' => $books->count(),
            'data' => $books->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'description' => $book->description,
                    'author' => $book->author,
                    'publisher' => $book->publisher,
                    'published_at' => $book->published_at,
                    'book_cover' => $book->book_cover,
                    'availability_status' => $book->availability_status,
                    'rating' => [
                        'average' => round($book->rating_average ?? 0, 1),
                        'total_reviews' => $book->total_reviews
                    ],
                    'categories' => $book->categories->map(function($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name
                        ];
                    })
                ];
            }),
        ]);
    }

    public function detailBook(Request $request, $id)
    {
        $name = $request->query('name');
        $user = User::where('name', $name)->first();

        // Base query to retrieve the book with relationships
        $query = Books::with(['categories', 'borrowRecords', 'bookReviews'])
            ->where('id', $id);

        // Add favorite_book count if user is logged in
        if ($user) {
            $query->withCount([
                'bookCollections as favorite_book' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
            ]);
        }

        // Calculate average rating using withAvg
        $query->withAvg('bookReviews as rating_average', 'rating');

        // Execute the query
        $book = $query->first();

        // If no book is found, return a 404 response
        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        // Get total reviews count
        $totalReviews = $book->bookReviews->count();

        // Format the response
        return response()->json([
            'data' => [
                'id' => $book->id,
                'title' => $book->title,
                'description' => $book->description,
                'author' => $book->author,
                'publisher' => $book->publisher,
                'published_at' => $book->published_at,
                'book_cover' => $book->book_cover,
                'rating' => [
                    'average' => round($book->rating_average ?? 0, 1), // Round to 1 decimal place
                    'total_reviews' => $totalReviews
                ],
                'availability_status' => $book->availability_status,
                'favorite_book' => $user ? $book->favorite_book > 0 : null,
                'categories' => $book->categories->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name
                    ];
                })
            ],
        ]);
    }

    public function borrowBook(Request $request, $id)
    {
        $book = Books::with('borrowRecords')
        ->where('id', $id)
        ->first();
        
        $user = Auth::user();

        if (!$book) {
            return response()->json([
                'message' => 'Buku tidak ditemukan.'
            ], 404);
        }

        if ($book->availability_status == 'Buku Sedang Dipinjam')
        {
            return response()->json([
                'message' => 'Mohon maaf buku sedang dipinjam!'
            ], 403);
        }

        $borrow = BorrowRecords::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrowed_at' => now(),
            'borrow_status' => 'borrowed',
        ]);

        if ($borrow)
        {
            return response()->json([
                'message' => 'Buku berhasil dipinjam. Silahkan datang ke petugas untuk konfirmasi!'
            ], 200);
        }
    }

    public function addFavoriteBook($id)
    {
        $book = Books::where('id', $id)
        ->first();

        if (!$book)
        {
            return response()->json([
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }
        
        $user = Auth::user();

        BookCollections::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        return response()->json([
            'message' => 'Berhasil menambahkan ke koleksi buku'
        ], 200);
    }

    public function removeFavoriteBook($id)
    {
        $book = Books::where('id', $id)
        ->first();

        if (!$book)
        {
            return response()->json([
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }
        
        $user = Auth::user();
        
        $collection = BookCollections::where('user_id', $user->id)
        ->where('book_id', $book->id)
        ->first();
        
        if (!$collection)
        {
            return response()->json([
                'message' => 'Koleksi buku tidak ada'
            ], 404);
        }

        $collection->delete();

        return response()->json([
            'message' => 'Berhasil menghapus koleksi buku'
        ], 200);
    }

    public function getPopularBook()
    {
        $books = Books::withCount('borrowRecords')
        ->orderBy('borrow_records_count', 'desc')
        ->take(4)
        ->get();

        return response()->json([
            "message" => "Daftar buku populer berhasil diambil",
            "data" => $books
        ]);
    }
}
