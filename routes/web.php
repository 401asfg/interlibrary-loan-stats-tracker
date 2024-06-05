<?php

/*
 * Author: Michael Allan
 */

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

Route::get('/', function () {
    return view('index');
});

Route::get('ill-requests', [ILLRequestController::class, 'index']);
Route::post('ill-requests', [ILLRequestController::class, 'store']);

Route::get('ill-requests/create', [ILLRequestController::class, 'create']);
Route::get('ill-requests/records', [ILLRequestController::class, 'records']);

Route::get('ill-requests/{id}', [ILLRequestController::class, 'show']);
Route::delete('ill-requests/{id}', [ILLRequestController::class, 'destroy']);
Route::put('ill-requests/{id}', [ILLRequestController::class, 'update']);

Route::get('ill-requests/{id}/edit', [ILLRequestController::class, 'edit']);

Route::get('libraries', [LibraryController::class, 'index'])->name('libraries');
Route::get('libraries/{id}', [LibraryController::class, 'show']);
