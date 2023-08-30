<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StorageController;

use App\Models\Directory;
use App\Models\File;

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
    // Debug
    Route::get('user', function(Request $request) {
        return $request->user();
    });

    Route::name('node.')->prefix('node/')->group(function () {
        $controllerInstance = new StorageController();

        // CRUDX
        Route::post('create/{node}', [StorageController::class, 'createNode'])
            ->name('create')->middleware('can:create,node');

        Route::get('read/file/{node}', [StorageController::class, 'readFile'])
            ->name('file.read')->middleware('can:read,node');

        Route::get('read/directory/{node}', [StorageController::class, 'readDirectory'])
            ->name('directory.read')->middleware('can:read,node');

        Route::get('update/file/{node}/{permissions}/{affected_user_id?}',
            function(Request $request, File $node, string $permissions, int $affected_user_id = null) use ($controllerInstance) {
                return $controllerInstance->updateNode($request, $node, $permissions, $affected_user_id);
            })
            ->name('file.update')->middleware('can:update,node,permissions,affected_user_id');

        Route::get('update/directory/{node}/{permissions}/{affected_user_id?}',
            function(Request $request, Directory $node, string $permissions, int $affected_user_id = null) use ($controllerInstance) {
                return $controllerInstance->updateNode($request, $node, $permissions, $affected_user_id);
            })
            ->name('file.update')->middleware('can:update,node,permissions,affected_user_id');

        Route::get('delete/file/{node}', function(File $node) use ($controllerInstance) {
            return $controllerInstance->deleteNode($node);
        })->name('file.delete')->middleware('can:delete,node');

        Route::get('delete/directory/{node}', function(Directory $node) use ($controllerInstance) {
            return $controllerInstance->deleteNode($node);
        })->name('directory.delete')->middleware('can:delete,node');

        Route::get('execute/{node}', [StorageController::class, 'executeFile'])
            ->name('execute')->middleware('can:execute,node');

        // OTHER
        Route::get('mount', [StorageController::class, 'mount'])
            ->name('mount');

        Route::get('move/{destination}/file/{child}', function(Directory $destination, File $child) use ($controllerInstance) {
            return $controllerInstance->moveNode($destination, $child);
        })->name('file.move')->middleware('can:move,destination,child');

        Route::get('move/{destination}/directory/{child}', function(Directory $destination, Directory $child) use ($controllerInstance) {
            return $controllerInstance->moveNode($destination, $child);
        })->name('directory.move')->middleware('can:move,destination,child');
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
