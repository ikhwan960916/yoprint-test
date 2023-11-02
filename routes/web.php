<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevAuthController;
use App\Http\Controllers\ProductController;

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
    return redirect()->route('auth.login.view');
});

Route::prefix('/auth')->group(function(){
    Route::get('/login', [DevAuthController::class, 'showUserLogin'])->name('auth.login.view');
    Route::post('/login/{user_id}', [DevAuthController::class , 'login'])->name('auth.login');
    Route::get('/logout', [DevAuthController::class , 'logout'])->name('auth.logout');
});

Route::group(['middleware' => 'auth'], function () {
    Route::prefix('/product')->group(function(){
        Route::get('/upload-csv', [ProductController::class, 'showUploadProductView'])->name('product.upload-csv.view');
        Route::post('/upload-csv', [ProductController::class, 'uploadCSV'])->name('product.upload-csv');
    });
});


