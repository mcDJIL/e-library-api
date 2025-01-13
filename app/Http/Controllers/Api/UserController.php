<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // Get all users
    public function index()
    {
        $users = User::where('role', 'admin')->orderBy('username', 'asc')->get();
        return response()->json([
            'message' => 'Daftar admin berhasil diambil',
            'data' => $users
        ]);
    }

    // Create a new user
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validation->fails())
            {
                return response()->json([
                    'message' => Str::ucfirst($validation->errors()->first()),
                ], 422);
            }

        // Create user with role 'admin'
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'address' => $request->address,
            'role' => 'admin', // Set default role as 'admin'
        ]);

        return response()->json([
            'message' => 'Admin berhasil dibuat',
            'data' => $user
        ], 201);
    }

    // Get a single user
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Admin tidak ditemukan'], 404);
        }

        return response()->json($user);
    }

    // Update a user
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Admin tidak ditemukan'], 404);
        }

        $validation = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validation->fails())
            {
                return response()->json([
                    'message' => Str::ucfirst($validation->errors()->first()),
                ], 422);
            }

        $user->update($request->only(['username', 'email', 'password', 'name', 'address']));
        return response()->json([
            'message' => 'Data Admin berhasil diperbarui',
            'data' => $user
        ]);
    }

    // Delete a user
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Admin tidak ditemukan'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Admin berhasil dihapus']);
    }

    public function getPeminjam()
    {
        $users = User::where('role', 'peminjam')->orderBy('username', 'asc')->get();
        return response()->json([
            'message' => 'Daftar peminjam berhasil diambil',
            'data' => $users
        ]);
    }

    // Create a new user
    public function addPeminjam(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validation->fails())
            {
                return response()->json([
                    'message' => Str::ucfirst($validation->errors()->first()),
                ], 422);
            }

        // Create user with role 'admin'
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'address' => $request->address,
            'role' => 'peminjam', // Set default role as 'admin'
        ]);

        return response()->json([
            'message' => 'Peminjam berhasil dibuat',
            'data' => $user
        ], 201);
    }

    // Get a single user
    public function showPeminjam($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Peminjam tidak ditemukan'], 404);
        }

        return response()->json($user);
    }

    // Update a user
    public function updatePeminjam(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Peminjam tidak ditemukan'], 404);
        }

        $validation = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validation->fails())
            {
                return response()->json([
                    'message' => Str::ucfirst($validation->errors()->first()),
                ], 422);
            }

        $user->update($request->only(['username', 'email', 'password', 'name', 'address']));
        return response()->json([
            'message' => 'Data Peminjam berhasil diperbarui',
            'data' => $user
        ]);
    }

    // Delete a user
    public function destroyPeminjam($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Peminjam tidak ditemukan'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Peminjam berhasil dihapus']);
    }
}
