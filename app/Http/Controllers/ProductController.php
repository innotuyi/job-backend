<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $query = DB::table('products')->toSql();
        $response = DB::select($query);
        
        return response()->json($response);
    }


    public function show($id)
    {
        try {
            $product = DB::table('products')
                ->select('*')
                ->where('id', $id)
                ->first();
    
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }
    
            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }
    

    public function create(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'required|string|',
                'photo1' => '|image|mimes:jpeg,png,jpg,gif|max:2048',
                'photo2' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'photo3' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'photo4' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $filenames = [];

            for ($i = 1; $i <= 4; $i++) {
                $file = $request->file('photo' . $i);

                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $filenames['photo' . $i] = $originalName;
                    $file->storeAs('public', $originalName);
                }
            }

            $data = [
                'name' => $request->name,
                'price' => $request->price,
                'categoryID' => $request->categoryID,
                'description' => $request->description,
                'photo1' => $filenames['photo1'] ?? null,
                'photo2' => $filenames['photo2'] ?? null,
                'photo3' => $filenames['photo3'] ?? null,
                'photo4' => $filenames['photo4'] ?? null,
            ];

            DB::table('products')->insert($data);

            $category = DB::table('products')->where('name', $request->name)->first();

            return response()->json($category, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while creating the product', 'error' => $e->getMessage()], 500);
        }
    }

    // public function update(Request $request, $productId)
    // {
    //     try {
    //         $rules = [
    //             'name' => 'required|string|max:255',
    //             'description' => '|string|max:255',
    //             'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //             'photo2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //             'photo3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //         ];

    //         $validator = Validator::make($request->all(), $rules);

    //         if ($validator->fails()) {
    //             return response()->json(['errors' => $validator->errors()], 400);
    //         }

    //         $product = Product::findOrFail($productId);

    //         // Delete existing images from storage
    //         foreach (['photo1', 'photo2', 'photo3', 'photo4'] as $photo) {
    //             if ($request->hasFile($photo) && $product->$photo) {
    //                 Storage::delete('public/' . $product->$photo);
    //             }
    //         }

    //         // Upload new images to storage
    //         $filenames = [];
    //         for ($i = 1; $i <= 3; $i++) {
    //             $file = $request->file('photo' . $i);

    //             if ($file && $file->isValid()) {
    //                 $originalName = $file->getClientOriginalName();
    //                 $filenames['photo' . $i] = $originalName;
    //                 $file->storeAs('public', $originalName);
    //             }
    //         }

    //         // Update product information in the database
    //         $product->update([
    //             'name' => $request->name,
    //             'price' => $request->price,
    //             'categoryID' => $request->categoryID,
    //             'description' => $request->description,
    //             'photo1' => $filenames['photo1'] ?? $product->photo1,
    //             'photo2' => $filenames['photo2'] ?? $product->photo2,
    //             'photo3' => $filenames['photo3'] ?? $product->photo3,
    //             'photo4' => $filenames['photo4'] ?? $product->photo3,
    //         ]);

    //         return response()->json(['message' => 'Product updated successfully', 'data' => $product], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'An error occurred while updating the product', 'error' => $e->getMessage()], 500);
    //     }
    // }

    public function update(Request $request, $productId)
{
    try {
        $rules = [
            'name' => '|string|max:255',
            'description' => '|string|max:255',
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Construct raw SQL update query
        $sql = "UPDATE products SET 
                    name = :name,
                    price = :price,
                    categoryID = :categoryID,
                    description = :description,
                    photo1 = :photo1,
                    photo2 = :photo2,
                    photo3 = :photo3
                WHERE id = :productId";

        // Bind values to the placeholders
        $bindings = [
            'name' => $request->name,
            'price' => $request->price,
            'categoryID' => $request->categoryID,
            'description' => $request->description,
            'photo1' => $filenames['photo1'] ?? null,
            'photo2' => $filenames['photo2'] ?? null,
            'photo3' => $filenames['photo3'] ?? null,
            'productId' => $productId,
        ];

        // Execute the raw SQL query
        DB::update($sql, $bindings);

        return response()->json(['message' => 'Product updated successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occurred while updating the product', 'error' => $e->getMessage()], 500);
    }
}


    public function destroy($id)
    {
        $job = Product::findOrFail($id);
        $job->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function updateCategory(Request $request, $id)
    {
        try {
            $category = Product::find($id);

            if (!$category) {
                return response()->json(['message' => 'category not found'], 404);
            }

            $data = $request->all();
            $category->update($data);
            return response()->json(['message' => 'category updated successfully', 'data' => $category], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the cooperative', 'error' => $e->getMessage()], 500);
        }
    }
}
