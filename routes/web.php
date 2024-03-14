<?php

/*
 * Author: Michael Allan
 */

// TODO: write unit tests

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
Route::get('create', [ILLRequestController::class, 'create']);

Route::post('/', [ILLRequestController::class, 'store']);
Route::get('/show/{id}', [ILLRequestController::class, 'show']);
// FIXME: remove show from route

Route::delete('{id}', [ILLRequestController::class, 'destroy']);

Route::get('{id}/edit', [ILLRequestController::class, 'edit']);
Route::put('{id}', [ILLRequestController::class, 'update']);

Route::get('libraries', [LibraryController::class, 'index'])->name('libraries');
Route::get('libraries/{id}', [LibraryController::class, 'show']);
