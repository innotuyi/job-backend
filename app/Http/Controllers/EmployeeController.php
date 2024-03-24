<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    
    public function create(Request $request) {


        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'contact' => 'required',
        ];
        
        $validator =Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

      return  DB::insert("INSERT INTO employees 
        (name, contact, email) 
        VALUES (?, ?, ?)", [$request->name, $request->contact, $request->email]);

    }


    public function employee() {


        $employees = DB::table('employees')->get();

        return response()->json($employees);
    }
}
