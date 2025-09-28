<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    // Create a new category
    public function store(Request $request)
    {
        $name = $request->input('name');

        DB::insert('INSERT INTO categories (name, created_at, updated_at) VALUES (?, ?, ?)', [
            $name,
            now(),
            now()
        ]);

        return response()->json(['message' => 'Category created successfully'], 201);
    }

    // Get all categories
    public function index()
    {
        $categories = DB::select('SELECT * FROM categories');
        return response()->json($categories, 200);
    }

    // Get a specific category by ID
    public function show($id)
    {
        $category = DB::select('SELECT * FROM categories WHERE id = ?', [$id]);

        if (empty($category)) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category[0], 200);
    }

    // Update a specific category by ID
    public function update(Request $request, $id)
    {
        $name = $request->input('name');

        $updated = DB::update('UPDATE categories SET name = ?, updated_at = ? WHERE id = ?', [
            $name,
            now(),
            $id
        ]);

        if ($updated) {
            return response()->json(['message' => 'Category updated successfully'], 200);
        }

        return response()->json(['message' => 'Category not found'], 404);
    }

    // Delete a specific category by ID
    public function destroy($id)
    {
        $deleted = DB::delete('DELETE FROM categories WHERE id = ?', [$id]);

        if ($deleted) {
            return response()->json(['message' => 'Category deleted successfully'], 200);
        }

        return response()->json(['message' => 'Category not found'], 404);
    }
}