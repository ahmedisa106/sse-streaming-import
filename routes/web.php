<?php

use App\Http\Controllers\User\ImportController;
use Illuminate\Support\Facades\Route;

Route::view('import-users', 'users.import')->name('users.import');
Route::post('import', [ImportController::class, 'import'])->name('import');
Route::get('imports/{import}', [ImportController::class, 'show'])->name('imports.show');
