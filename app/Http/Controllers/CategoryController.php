<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{



    public function index() {

        $response = Category::all();

        return response()->json($response);


    }

    public function show($id) {

         $response = Category::find($id);

         if (!$response) {

            return response()->json(['message' => 'Inspection not found'], 404);

        
         }
         return response()->json($response);


    }

    public function create(Request $request)
    {

        try {
            $rules = [
                'name' => 'required|string|max:255', 
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
    
            $category = Category::create($request->all());
            return response()->json(['message' => 'category created successfully', 'data' => $category], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while creating the inspection', 'error' => $e->getMessage()], 500);
        }


    }

    public function update(Request $request, $id) {
        try {
            $category = Category::find($id);
    
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
