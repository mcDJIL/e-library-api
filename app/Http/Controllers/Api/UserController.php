<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Get all users
    public function index()
    {
        $users = User::where('role', 'admin')->get();
        return response()->json([
            'message' => 'Daftar admin berhasil diambil',
            'data' => $users
        ]);
    }

    // Create a new user
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

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
            'message' => 'Pengguna berhasil dibuat',
            'data' => $user
        ], 201);
    }

    // Get a single user
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        return response()->json($user);
    }

    // Update a user
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        $user->update($request->only(['username', 'email', 'password', 'name', 'address']));
        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui',
            'data' => $user
        ]);
    }

    // Delete a user
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Pengguna berhasil dihapus']);
    }
}
