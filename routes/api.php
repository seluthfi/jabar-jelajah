<?php

use App\Http\Controllers\Api\DestinasiController;
use App\Http\Controllers\Api\RekomendasiController;
use Illuminate\Support\Facades\Route;

Route::get('/destinasi', [DestinasiController::class, 'index']);
Route::get('/destinasi/{id}', [DestinasiController::class, 'show']);
Route::get('/kota', [DestinasiController::class, 'kota']);
Route::post('/rekomendasi', [RekomendasiController::class, 'hitung']);
