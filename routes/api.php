<?php

use App\Http\Controllers\Admin\CompaniesController;
use App\Http\Controllers\Admin\PoliciesController;
use App\Http\Controllers\Admin\PolicyCategoriesController;
use App\Http\Controllers\Admin\PolicyWindowActionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Public\PolicyAckController;
use App\Http\Controllers\Public\PublicAckController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('policy-windows')->middleware('throttle:20,1')->group(function () {
    Route::post('{window}/identity-check', [PolicyAckController::class, 'verifyIdentity']);
    Route::post('{window}/acknowledge', [PolicyAckController::class, 'acknowledge']);
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    // ดู/ค้นหา ของ HR
    Route::middleware('can:view-policies')->group(function () {
        Route::get('companies', [CompaniesController::class, 'index']);
        Route::get('companies/select', [CompaniesController::class, 'select']);

        Route::get('policy-categories/', [PolicyCategoriesController::class, 'index']);
        Route::get('policy-categories/{category}', [PolicyCategoriesController::class, 'show']);
        Route::get('policy-categories/select', [PolicyCategoriesController::class, 'select']);

        Route::get('policies', [PoliciesController::class, 'index']);
        Route::get('policies/{policy}', [PoliciesController::class, 'show']);

        Route::get('policy-windows/{window}/qr.png', [PolicyWindowActionController::class, 'qr'])->name('admin.windows.qr');
        Route::post('policy-windows/{window}/announce-now', [PolicyWindowActionController::class, 'announceNow']);
    });

    // เพิ่ม/ลบ/แก้ไข
    Route::middleware('can:manage-policies')->group(function () {
        Route::post('companies', [CompaniesController::class, 'store']);
        Route::put('companies/{company}', [CompaniesController::class, 'update']);
        Route::delete('companies/{company}', [CompaniesController::class, 'destroy']);

        Route::post('policy-categories', [PolicyCategoriesController::class, 'store']);
        Route::put('policy-categories/{category}', [PolicyCategoriesController::class, 'update']);
        Route::delete('policy-categories/{category}', [PolicyCategoriesController::class, 'destroy']);

        Route::post('policies', [PoliciesController::class, 'store']);
        Route::put('policies/{policy}', [PoliciesController::class, 'update']);
        Route::delete('policies/{policy}', [PoliciesController::class, 'destroy']);

    });

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);
});

// Proteced routes
Route::group(['middleware' => 'auth:sanctum'], function () {});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
