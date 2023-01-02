<?php

use App\Http\Controllers\UserController;
use App\Models\Order;
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


Route::get("/report", [UserController::class, 'getReport'])->name('report');

Route::get("/autocomplete", [UserController::class, 'autocomplete'])->name('autocomplete');

Route::post("/report", [UserController::class, 'postReport'])->name('search');

Route::get("/top-100", [UserController::class, 'top100'])->name('top');
