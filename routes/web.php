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
