<?php

use App\Http\Controllers\Common\BaiduController;
use App\Http\Controllers\Common\WeiboController;
use App\Http\Controllers\Common\WeiXinController;
use App\Http\Controllers\Controller;
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

Route::match(['get', 'post'],'/', [WeiXinController::class,'index']);

Route::match(['get', 'post'],'first_valid', [WeiXinController::class,'firstValid']);
Route::match(['get', 'post'],'test', [WeiXinController::class,'test']);
Route::prefix('Weibo')->group(function () {
    Route::get('push', [WeiboController::class,'push']);
});

Route::prefix('Baidu')->group(function () {
    Route::get('callback', [BaiduController::class,'callback']);
});

