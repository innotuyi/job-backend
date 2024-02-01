<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'index']); // Get all jobs
    Route::get('/{id}', [JobController::class, 'show']); // Get a specific job by ID

    Route::post('/', [JobController::class, 'create']); // Create a new job
    Route::post('/{id}', [JobController::class, 'update']); // Update an existing job
    Route::delete('/{id}', [JobController::class, 'destroy']); // Delete a job
});


Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']); // Get all jobs
    Route::get('/{id}', [ProductController::class, 'show']); // Get a specific job by ID

    Route::post('/', [ProductController::class, 'create']); // Create a new job
    Route::post('/{id}', [ProductController::class, 'update']); // Update an existing job
    Route::delete('/{id}', [ProductController::class, 'destroy']); // Delete a job
});


Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']); // Get all jobs
    Route::get('/{id}', [CategoryController::class, 'show']); // Get a specific job by ID

    Route::post('/', [CategoryController::class, 'create']); // Create a new job
    Route::put('/{id}', [ProductController::class, 'update']); // Update an existing job
    Route::delete('/{id}', [ProductController::class, 'destroy']); // Delete a job
});


Route::get('/allJobs', [DashboardController::class, 'allJobs']);
Route::get('/allProducts', [DashboardController::class, 'allProducts']);


Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);


Route::get('/run-link-command', function () {
Artisan::call('storage:link');
    return 'Storage link created successfully.';
});
