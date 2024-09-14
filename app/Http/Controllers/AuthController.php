<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\TestInputRequest;
use App\Models\User;
use App\Service\AuthService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpParser\Node\Stmt\TryCatch;

class AuthController extends Controller
{
    public function __construct(protected AuthService $service) {}

    public function register(Request $request)
    {



        try {
            $rules = [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ];


            $validator = Validator::make($request->all(), $rules);










            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            //$this->service->register($request->name,  $request->email, $request->password);

            DB::insert("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())", [$request->name, $request->email,  Hash::make($request->password),]);

            return response()->json(["Users registered"]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function login(Request $request)
    {
        // Validate the incoming request
        $val = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]
        );

        if ($val->fails()) {
            return response()->json([
                'errors' => $val->errors()
            ], 422); // Unprocessable Entity HTTP response
        }

        // Attempt to find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists and verify the password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401); // Unauthorized HTTP response
        }

        // Create a token for the authenticated user
        $token = $user->createToken('myapptoken')->plainTextToken;

        // Return the token in response
        return response()->json([
            'token' => $token,
            'message' => 'Login successful'
        ], 200); // OK HTTP response
    }



    public function logout(Request $request)
    {

        if (Auth::check()) {

            $user = Auth::user();

            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }


    public function resetPassword(Request $request)
    {

        $rules = [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }


        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password reset successfully']);
    }
}
