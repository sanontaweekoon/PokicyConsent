<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PolicyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/logout-beacon', [AuthController::class, 'logoutBeacon']);

// Proteced routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::prefix('backend')->name('backend.')->group(function () {
        Route::resource('policys', PolicyController::class);
    });
    // Logout
    Route::post('logout', [AuthController::class, 'logout']);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });