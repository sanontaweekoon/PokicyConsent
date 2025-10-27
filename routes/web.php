<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Public\PublicAckController;
use Laravel\Socialite\Facades\Socialite;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login/microsoft', function () {
    return Socialite::driver('azure')
        ->scopes(['openid','profile','email'])
        ->redirect();
});

Route::get('/dev/ack/{window}', [PublicAckController::class, 'show'])->name('ack.window.dev');

Route::get('/ack/{window}', [PublicAckController::class, 'show'])
    ->name('ack.window')
    ->middleware('signed');

Route::get('/login/microsoft/callback', [AuthController::class, 'microsoftCallback']);

Route::view('/{any}', 'app')->where('any', '^(?!api/).*');