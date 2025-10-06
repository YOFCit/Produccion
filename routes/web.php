<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelController;

Route::get('/excel', [ExcelController::class, 'index'])->name('excel.index');
Route::post('/excel/upload', [ExcelController::class, 'upload'])->name('excel.upload');
Route::post('/excel/save', [ExcelController::class, 'save'])->name('excel.save');

Route::get('/', function () {
  return view('welcome');
});
