<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BerkasPrrController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Dashboard Utama ───────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});

// ── Admin ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/berkas',                [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/import-excel',         [AdminController::class, 'importExcel'])->name('import');
    Route::post('/berkas',               [BerkasPrrController::class, 'store'])->name('berkas.store');
    Route::put('/berkas/{berkasPrr}',    [BerkasPrrController::class, 'update'])->name('berkas.update');
    Route::delete('/berkas/{berkasPrr}', [BerkasPrrController::class, 'destroy'])->name('berkas.destroy');
});

// ── ULP ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:ulp'])->prefix('ulp')->name('ulp.')->group(function () {
    Route::get('/berkas',                [BerkasPrrController::class, 'ulpBerkas'])->name('berkas');
    Route::post('/berkas',               [BerkasPrrController::class, 'store'])->name('berkas.store');
    Route::put('/berkas/{berkasPrr}',    [BerkasPrrController::class, 'update'])->name('berkas.update');
    Route::delete('/berkas/{berkasPrr}', [BerkasPrrController::class, 'destroy'])->name('berkas.destroy');
});

// ── Profil ────────────────────────────────────────────────────────
Route::middleware('auth')->prefix('profil')->name('profil.')->group(function () {
    Route::get('/',         [ProfileController::class, 'edit'])->name('edit');
    Route::put('/',         [ProfileController::class, 'update'])->name('update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
});