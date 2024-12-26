<?php

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

use App\Http\Controllers\FileImportController;
use App\Http\Controllers\ImportedDataController;


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['role:admin'])->group(function () {
    Route::resource('users', 'UserController');
    Route::resource('permissions', 'PermissionController');
});

Route::get('/import', [FileImportController::class, 'index'])->name('imports.index');
Route::post('/import/process', [FileImportController::class, 'process'])->name('imports.process');

Route::get('/files/{type}/{file}', [ImportedDataController::class, 'index'])->name('files.index');
Route::delete('/files/{type}/{file}/{id}', [ImportedDataController::class, 'destroy'])->name('files.delete');
Route::get('/files/{type}/{file}/{id}/logs', [ImportedDataController::class, 'logs'])->name('files.logs');
Route::get('/files/{type}/{file}/export', [ImportedDataController::class, 'export'])->name('files.export');

