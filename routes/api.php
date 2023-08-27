<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StorageController;

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

// Gaurded API calls
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('user', function(Request $request) {
        return $request->user();
    });
    Route::name('node.')->prefix('node/')->group(function () {

        Route::post('create/{node}', [StorageController::class, 'createNode'])
            ->name('create')->middleware('can:create,node');

        Route::get('read/file/{node}', [StorageController::class, 'readFile'])
            ->name('file.read')->middleware('can:read,node');

        Route::get('read/directory/{node}', [StorageController::class, 'readDirectory'])
            ->name('directory.read')->middleware('can:read,node');

        Route::get('update/file/{node}/{permissions}', [StorageController::class, 'updateFile'])
            ->name('file.update')->middleware('can:update,node');

        Route::get('update/directory/{node}/{permissions}', [StorageController::class, 'updateDirectory'])
            ->name('directory.update')->middleware('can:update,node');

        Route::get('delete/file/{node}', [StorageController::class, 'deleteFile'])
            ->name('file.delete')->middleware('can:delete,node');

        Route::get('delete/directory/{node}', [StorageController::class, 'deleteDirectory'])
            ->name('directory.delete')->middleware('can:delete,node');

        Route::get('execute/{node}', [StorageController::class, 'executeFile'])
            ->name('execute')->middleware('can:execute,node');

    });

    Route::name('auth.')->prefix('auth')->group(function () {
		Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    });
});

// API Calls that are not guarded
Route::group([], function () {
	Route::name('auth.')->prefix('auth')->group(function () {
		Route::post('login', [LoginController::class, 'login'])->name('login');
    });
});
