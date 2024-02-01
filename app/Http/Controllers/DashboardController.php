<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    

 

    public function allProducts() {

       try {
            $query = "SELECT COUNT(*) as total FROM products";
            $result = DB::selectOne($query);
    
            return response()->json($result->total);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function allJobs() {

        try {
             $query = "SELECT COUNT(*) as total FROM jobs";
             $result = DB::selectOne($query);
     
             return response()->json($result->total);
         } catch (\Exception $e) {
             return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
         }
     }
}
