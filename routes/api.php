<?php

/* ส่วนของ Admin จัดการระบบ */

use App\Http\Controllers\Admin\CompaniesController;
// ส่วนของการสร้างนโยบาย เพิ่ม แก้ไข ลบ 
use App\Http\Controllers\Admin\PoliciesController;
// ส่วนของประเภทนโยบาย
use App\Http\Controllers\Admin\PolicyCategoriesController;
// ส่วนของการสร้าง QrCode เมื่อมีการประกาศนโยบาย
use App\Http\Controllers\Admin\PolicyWindowActionController;
// ส่วนของการเปิด-ปิด การรับทราบของนโยบาย
use App\Http\Controllers\Admin\PolicyWindowController;
// ส่วนของการดึงรายชื่อพนักงาน
use App\Http\Controllers\Admin\EmployeeController;
// ส่วนของการจัดการกลุ่มผู้รับ
use App\Http\Controllers\Admin\RecipientGroupController;

// ส่วนของการเข้าใช้งานระบบ เฉพาะ Admin
use App\Http\Controllers\AuthController;

/* ส่วนของพนักงานที่จะต้องรับทราบ */
// ส่วนของพนักงาน ที่จะต้องยืนยันตัวตน และ รับทราบนโยบาย 
use App\Http\Controllers\Public\PolicyAckController;
// ส่วนของการดึงเอาเนื้อหานโยบายมาแสดง แต่ละหน้าต่างนโยบาย
use App\Http\Controllers\Public\PolicyContentController;

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
    Route::get('{window}/qr', [PolicyWindowActionController::class, 'qr']);
    Route::get('{window}/qr-svg', [PolicyWindowActionController::class, 'qrSvg']);

    Route::post('{window}/identity-check', [PolicyAckController::class, 'verifyIdentity']);
    Route::post('{window}/acknowledge', [PolicyAckController::class, 'acknowledge']);
});

Route::get('policy-windows/{window}/content', [PolicyContentController::class, 'show']);

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

        Route::post('policy-windows/{window}/announce', [PolicyWindowActionController::class, 'announceNow']);
        Route::get('policy-windows/{window}/qr-download', [PolicyWindowActionController::class, 'qrDownload']);

        Route::put('policy-windows/{window}/toggle', [PolicyWindowController::class, 'toggle']);
        Route::put('policy-windows/{window}/open', [PolicyWindowController::class, 'open']);
        Route::put('policy-windows/{window}/close', [PolicyWindowController::class, 'close']);

        Route::apiResource('recipient-groups', RecipientGroupController::class);

        Route::get('employees', [EmployeeController::class, 'index']);
        Route::get('employees/search', [EmployeeController::class, 'search']);
        Route::get('employees/departments', [EmployeeController::class, 'departments']);
        Route::get('employees/by-department', [EmployeeController::class, 'byDepartment']);
    });

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);
});

// Proteced routes
Route::group(['middleware' => 'auth:sanctum'], function () {});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
