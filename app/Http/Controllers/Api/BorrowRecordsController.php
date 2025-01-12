<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BorrowRecords;
use Illuminate\Http\Request;

class BorrowRecordsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $borrow_records = BorrowRecords::with(['user', 'book'])->get();

        return response()->json([
            'message' => 'Daftar peminjaman berhasil diambil.',
            'data' => $borrow_records
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the status.
     */
    public function update(Request $request, string $id)
    {
        $borrow_records = BorrowRecords::find($id);

        if (!$borrow_records) {
            return response()->json(['message' => 'Peminjaman tidak ditemukan'], 404);
        }

        $request->validate([
            'borrow_status' => 'required|in:returned',
        ]);

        $borrow_records->update([
            'returned_at' => now(),
            'borrow_status' => 'returned',
        ]);

        return response()->json([
            'message' => 'Status peminjaman berhasil diperbarui',
            'data' => $borrow_records
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
