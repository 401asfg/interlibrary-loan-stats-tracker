<?php

// TODO: make library field a nullable foreign key to static library database
// TODO: add dataset for library autocompletion
// TODO: implement library name autocompletion
// TODO: make fields disappear based on state of previous input
// TODO: implement view records
// TODO: allow users to write on multiple rows in description box
// TODO: sticky fields

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
