<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NodeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Gaurded API calls
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::name('node.')->prefix('node/')->group(function () {

        Route::get('create/{node}', [NodeController::class, 'createNode'])
            ->name('create')->middleware('can:create,node');

        Route::get('read/{node}', [NodeController::class, 'readNode'])
            ->name('read')->middleware('can:read,node');

        Route::get('update/{node}', [NodeController::class, 'updateNode'])
            ->name('update')->middleware('can:update,node');

        Route::get('delete/{node}', [NodeController::class, 'deleteNode'])
            ->name('delete')->middleware('can:delete,node');

        Route::get('execute/{node}', [NodeController::class, 'executeNode'])
            ->name('execute')->middleware('can:execute,node');

    });
});

// API Calls that are not guarded
Route::group([], function () {
	Route::name('auth.')->prefix('auth')->group(function () {
		Route::post('login', [LoginController::class, 'login'])->name('login');
    });
});
