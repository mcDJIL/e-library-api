<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'username' => 'required|min:3|unique:users',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'name' => 'required|min:8',
            'address' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => Str::ucfirst($validation->errors()->first())
            ], 422);
        }

        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Enkripsi password
                'name' => $request->name,
                'address' => $request->address,
            ]);

            return response()->json([
                'message' => 'Daftar akun berhasil',
                'data' => $user
            ], 201); // Menggunakan status HTTP 201 (Created)
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Daftar akun gagal, silahkan coba lagi: ' . $e,
            ], 500); // Menggunakan status HTTP 500 (Internal Server Error)
        }
    }

    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validation->fails())
        {
            return response()->json([
                'message' => Str::ucfirst($validation->errors()->first())
            ], 422);
        }

        $credentials = $request->only(['email', 'password']);

        if (Auth::attempt($credentials))
        {
            $user = Auth::user();

            $token = $user->createToken('API Token')->plainTextToken;

            $user['token'] = $token;

            return response()->json([
                'message' => 'Login berhasil',
                'data' => $user
            ], 200);
        }
        else
        {
            return response()->json([
                'message' => 'Email atau password anda salah'
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        $user->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logout berhasil'
        ], 200);
    }
}
