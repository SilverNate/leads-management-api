<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Http\Middleware\AuthenticateApi; // Import the custom middleware

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

// Group routes that require API authentication
Route::middleware([AuthenticateApi::class])->group(function () {
    // Route to store lead data
    Route::post('/leads', [LeadController::class, 'store']);

    // Route to retrieve all leads
    Route::get('/leads', [LeadController::class, 'index']);

    // Route to retrieve details of a specific lead by ID
    Route::get('/leads/{id}', [LeadController::class, 'show']);
});

// Example route that does NOT require authentication (for testing purposes, if needed)
// Route::get('/status', function () {
//     return response()->json(['message' => 'API is running']);
// });


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
