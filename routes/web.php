<?php

// TODO: implement edit feature
// TODO: ensure that filling out a field, then making that field not visible erases the given value for that field
// TODO: write unit tests
// TODO: make fields disappear based on state of previous input

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
Route::get('/create', [ILLRequestController::class, 'create'])->name('create');

Route::post('/', [ILLRequestController::class, 'store']);
Route::get('/show/{id}', [ILLRequestController::class, 'show'])->name('show');

Route::delete('/{id}', [ILLRequestController::class, 'destroy']);

Route::get('/libraries', [LibraryController::class, 'index'])->name('libraries');
Route::get('/libraries/show/{id}', [LibraryController::class, 'show'])->name('libraries/show');
