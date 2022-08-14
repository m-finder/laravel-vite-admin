<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\SmsCodeController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::post('captcha', function () {
    return response()->json([
        'data' => app('captcha')->create('default', true),
        'msg' => '操作成功',
        'code' => 200
    ]);
})->name('captcha');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('verification_code', [SmsCodeController::class, 'store'])->name('sms.code');
