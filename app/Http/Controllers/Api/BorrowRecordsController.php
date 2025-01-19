<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BorrowRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if ($request->borrow_status == 'borrowed')
        {
            $borrow_records->returned_at = null;
            $borrow_records->borrow_verif = $request->borrow_verif;
            $borrow_records->save();
        }

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

    public function borrow_verification(Request $request, string $id)
    {
        $borrow_records = BorrowRecords::find($id);

        if (!$borrow_records) {
            return response()->json(['message' => 'Peminjaman tidak ditemukan'], 404);
        }

        $request->validate([
            'borrow_verif' => 'required',
        ]);

        $borrow_records->borrow_status = $request->borrow_status;
        $borrow_records->borrow_verif = $request->borrow_verif;
        $borrow_records->save();
        
        if ($request->borrow_verif == 'ditolak')
        {
            $borrow_records->returned_at = now();
            $borrow_records->save();

            return response()->json([
                'message' => 'Peminjaman berhasil ditolak',
                'data' => $borrow_records
            ], 200);
        }
        else
        {
            return response()->json([
                'message' => 'Peminjaman berhasil disetujui',
                'data' => $borrow_records
            ], 200);    
        }

    }

    public function report(Request $request)
    {
        $query = BorrowRecords::with(['user', 'book']);
        
        // Filter borrow_status
        if ($request->has('borrow_status') && !empty($request->query('borrow_status'))) {
            $borrow_status = $request->query('borrow_status');
            $query->where('borrow_status', $borrow_status)
                ->where('borrow_verif', 'disetujui');
        }

        // Filter borrow_verif
        if ($request->has('borrow_verif') && !empty($request->query('borrow_verif'))) {
            $borrow_verif = $request->query('borrow_verif');

            $query->where('borrow_verif', $borrow_verif);

            // Tambahkan kondisi borrow_status hanya jika borrow_verif ada
            if ($borrow_verif == 'menunggu') {
                $query->where('borrow_status', 'borrowed');
            } 
            elseif ($borrow_verif == 'disetujui') {
                $query->where('borrow_status', 'borrowed');
            } 
            else {
                $query->where('borrow_status', 'returned');
            }
        }

        // Filter by date range
        if ($request->has('start_date') || $request->has('end_date')) {
            $start_date = $request->query('start_date');
            $end_date = $request->query('end_date');

            if ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            }

            if ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            }
        }

        // Tambahkan default sort untuk memastikan data diurutkan
        $query->orderBy('created_at', 'desc');

        // Ambil data
        $borrow_records = $query->get();

        return response()->json([
            'message' => 'Daftar peminjaman berhasil diambil.',
            'data' => $borrow_records
        ]);
    }

    public function getBorrowHistory()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
                'status' => 401
            ], 401);
        }

        $borrow_records = BorrowRecords::with(['user', 'book'])
        ->where('user_id', $user->id)
        ->get();

        $borrow_records->map(function($borrow_record) {
            $borrow_record->book->availability_status = $borrow_record->book->availability_status;
        });

        return response()->json([
            'message' => 'riwayat peminjaman berhasil diambil.',
            'data' => $borrow_records
        ]);
    }
}
