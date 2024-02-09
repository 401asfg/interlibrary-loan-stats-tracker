<?php

// TODO: make fields disappear based on state of previous input
// TODO: implement view records
// TODO: sticky fields

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ILLRequestController;
use App\Http\Controllers\LibraryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [ILLRequestController::class, 'index']);
Route::post('/', [ILLRequestController::class, 'store']);
Route::delete('/{id}', [ILLRequestController::class, 'destroy']);

Route::get('/libraries', [LibraryController::class, 'index'])->name('libraries');
Route::get('/libraries/{id}', [LibraryController::class, 'index']);
