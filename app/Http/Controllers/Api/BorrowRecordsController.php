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
        $borrow_record = BorrowRecords::with(['user', 'book'])
        ->where('id', $id)
        ->get();

        return response()->json([
            'message' => 'Data peminjaman berhasil diambil.',
            'data' => $borrow_record
        ]);
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
            'borrow_status' => 'required',
        ]);

        $borrow_records->update([
            'returned_at' => now(),
            'borrow_status' => $request->borrow_status,
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

    public function report()
    {
        $borrow_records = BorrowRecords::with(['user', 'book'])
        ->whereNotNull('returned_at')
        ->get();

        return response()->json([
            'message' => 'Daftar peminjaman berhasil diambil.',
            'data' => $borrow_records
        ]);
    }
}
