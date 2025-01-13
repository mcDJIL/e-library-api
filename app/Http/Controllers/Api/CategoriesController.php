<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    // Get all categories
    public function index()
    {
        $categories = Categories::orderBy('name', 'asc')->get();

        return response()->json([
            'message' => 'Daftar kategori berhasil diambil.',
            'data' => $categories
        ]);
    }

    // Create a new category
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validation->fails())
            {
                return response()->json([
                    'message' => Str::ucfirst($validation->errors()->first()),
                ], 422);
            }

        $category = Categories::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Kategori berhasil dibuat',
            'data' => $category
        ], 201);
    }

    // Get a specific category
    public function show($id)
    {
        $category = Categories::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        return response()->json($category, 200);
    }

    // Update a category
    public function update(Request $request, $id)
    {
        $category = Categories::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validation->fails())
            {
                return response()->json([
                    'message' => Str::ucfirst($validation->errors()->first()),
                ], 422);
            }

        $category->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui',
            'data' => $category
        ], 200);
    }

    // Delete a category
    public function destroy($id)
    {
        $category = Categories::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus'], 200);
    }
}
