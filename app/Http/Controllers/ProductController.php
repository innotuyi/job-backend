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

        // $response = Product::join('Category', 'Product.categoryID', '=', 'Category.id')
        //     ->select('Product.*', 'Category.name as category_name', 'Product.description')
        //     ->orderBy('Product.created_at', 'desc')
        //     ->get();

        //    return response()->json($response);

        $response = Product::all();

           return response()->json($response);
    }

    public function show($id)
    {
    //     $propertyDetail = DB::selectOne('SELECT Product.id, Product.name as product_name, Product.description, Product.photo1, Product.photo2, Product.categoryID, Category.name as Category_name FROM Product
    // INNER JOIN Category ON
    // Product.categoryID = Category.id
    
    //  WHERE Product.id = ?', [$id]);

    //     if (!empty($propertyDetail)) {

    //         $propertyDetail = json_decode(json_encode($propertyDetail), true);
    //     }

    $product = Product::findOrFail($id);

    return response()->json($product);

    }


    public function create(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'price' => '|integer|max:255',
                'description' => 'required|string|max:255',
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

            $category = Product::create([
                'name' => $request->name,
                'price' => $request->price,
                'categoryID' => $request->categoryID,
                'description' => $request->description,
                'photo1' => $filenames['photo1'] ?? null,
                'photo2' => $filenames['photo2'] ?? null,
                'photo3' => $filenames['photo3'] ?? null,
                'photo4' => $filenames['photo4'] ?? null,

            ]);

            return response()->json($category, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while creating the product', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $productId)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'price' => '|decimal|max:255',
                'categoryID' => 'required|integer|max:255',
                'description' => 'required|string|max:255',
                'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'photo2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'photo3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ];
            

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $product = Product::findOrFail($productId);

            // Delete existing images from storage
            foreach (['photo1', 'photo2', 'photo3', 'photo4'] as $photo) {
                if ($request->hasFile($photo) && $product->$photo) {
                    Storage::delete('public/' . $product->$photo);
                }
            }

            // Upload new images to storage
            $filenames = [];
            for ($i = 1; $i <= 3; $i++) {
                $file = $request->file('photo' . $i);

                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $filenames['photo' . $i] = $originalName;
                    $file->storeAs('public', $originalName);
                }
            }

            // Update product information in the database
            $product->update([
                'name' => $request->name,
                'price' => $request->price,
                'categoryID' => $request->categoryID,
                'description' => $request->description,
                'photo1' => $filenames['photo1'] ?? $product->photo1,
                'photo2' => $filenames['photo2'] ?? $product->photo2,
                'photo3' => $filenames['photo3'] ?? $product->photo3,
                'photo4' => $filenames['photo4'] ?? $product->photo3,

            ]);

            return response()->json(['message' => 'Product updated successfully', 'data' => $product], 200);
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

            $data = $request->all(); // Corrected line
            $category->update($data);
            return response()->json(['message' => 'category updated successfully', 'data' => $category], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the cooperative', 'error' => $e->getMessage()], 500);
        }
    }
}
