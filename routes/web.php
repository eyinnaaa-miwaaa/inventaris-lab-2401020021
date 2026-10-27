<?php

use App\Http\Controllers\InventarisController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/inventaris'));

Route::get('/inventaris', [InventarisController::class, 'index'])->name('inventaris.index');
Route::post('/inventaris', [InventarisController::class, 'store'])->name('inventaris.store');
Route::get('/inventaris/{id}/edit', [InventarisController::class, 'edit'])->whereNumber('id')->name('inventaris.edit');
Route::put('/inventaris/{id}', [InventarisController::class, 'update'])->whereNumber('id')->name('inventaris.update');
Route::delete('/inventaris/{id}', [InventarisController::class, 'destroy'])->whereNumber('id')->name('inventaris.destroy');
