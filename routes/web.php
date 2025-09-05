<?php

use App\Http\Controllers\TcoloringController;
use App\Http\Controllers\TscoatingController;
use App\Http\Controllers\TsheatingController;
use App\Http\Controllers\TstrandingController;
use Illuminate\Support\Facades\Route;


//Ruta principal
Route::get('/', function () {
  return view('welcome');
})->name('inicio');

// Route::get('/Coloring', [TcoloringController::class, 'index'])->name('col');
// Route::get('/Coloring/Admin', [TcoloringController::class, 'indexAdmin'])->name('col.admin');

Route::get('/Stranding', [TstrandingController::class, 'index'])->name('sz');
Route::get('/Stranding/Admin', [TstrandingController::class, 'indexAdmin'])->name('sz.admin');

Route::get('/Sheating', [TsheatingController::class, 'index'])->name('sh');
Route::get('/Sheating/Admin', [TsheatingController::class, 'indexAdmin'])->name('sh.admin');

Route::get('/SecondaryCoating', [TscoatingController::class, 'index'])->name('sc');
Route::get('/SecondaryCoating/Admin', [TscoatingController::class, 'indexAdmin'])->name('sc.admin');
