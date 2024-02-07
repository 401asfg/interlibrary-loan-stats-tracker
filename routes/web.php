<?php

// TODO: implement view records
// TODO: add view records button to form page
// TODO: allow users to write on multiple rows in description box
// TODO: sticky fields
// TODO: add dataset for library autocompletion
// TODO: implement library name autocompletion

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ILLRequestController;

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
