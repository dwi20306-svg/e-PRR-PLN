<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminUP3\DashboardController as AdminDashboard;
use App\Http\Controllers\AdminUP3\BerkasPrrController as AdminBerkas;
use App\Http\Controllers\PetugasULP\DashboardController as PetugasDashboard;
use App\Http\Controllers\PetugasULP\BerkasPrrController as PetugasBerkas;

/*
|--------------------------------------------------------------------------
| Web Routes - Aplikasi e-PRR UP3 Banda Aceh
|--------------------------------------------------------------------------
*/

// ── Auth & Tamu ───────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Jembatan Halaman Utama (/) ────────────────────────────────────
// Menghindari tabrakan: Mengarahkan user secara otomatis berdasarkan role saat membuka web
Route::middleware('auth')->get('/', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('ulp.dashboard');
})->name('dashboard');


// ── Role: Admin UP3 (Dashboard Utama & Semua Berkas) ─────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Halaman Ringkasan/Grafik Utama (Screenshot 1)
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Tabel Semua Berkas & Filter Per ULP Klik Sidebar (Screenshot 2 & 3)
    Route::get('/berkas', [AdminBerkas::class, 'index'])->name('berkas.index');

    // Fitur Tambah, Edit, Hapus Berkas Sisi Admin
    Route::post('/berkas', [AdminBerkas::class, 'store'])->name('berkas.store');
    Route::put('/berkas/{berkasPrr}', [AdminBerkas::class, 'update'])->name('berkas.update');
    Route::delete('/berkas/{berkasPrr}', [AdminBerkas::class, 'destroy'])->name('berkas.destroy');

    // Fitur Import Excel (Logikanya sudah di dalam BerkasPrrController Admin)
    Route::post('/import-excel', [AdminBerkas::class, 'importExcel'])->name('import');

    // Fitur Download Error Import Excel (Logikanya sudah di dalam BerkasPrrController Admin)
    Route::get('/import-error/{file}',[AdminBerkas::class, 'downloadImportError'])->name('import.error');

    // Fitur Export Excel (Logikanya sudah di dalam BerkasPrrController Admin)
    Route::get('/export-excel', [AdminBerkas::class, 'exportExcel'])->name('export');
});


// ── Role: Petugas ULP (Dashboard Mandiri & Berkas Internal) ──────
Route::middleware(['auth', 'role:ulp'])->prefix('ulp')->name('ulp.')->group(function () {

    // Halaman Ringkasan/Grafik Mandiri Petugas ULP (Screenshot 4)
    Route::get('/dashboard', [PetugasDashboard::class, 'index'])->name('dashboard');

    // Tabel Berkas Internal ULP (Screenshot 5)
    Route::get('/berkas', [PetugasBerkas::class, 'index'])->name('berkas.index');

    // Fitur Tambah, Edit, Hapus Berkas Sisi Petugas (Terkunci Otomatis ke ULP Sendiri)
    Route::post('/berkas', [PetugasBerkas::class, 'store'])->name('berkas.store');
    Route::put('/berkas/{berkasPrr}', [PetugasBerkas::class, 'update'])->name('berkas.update');
    Route::delete('/berkas/{berkasPrr}', [PetugasBerkas::class, 'destroy'])->name('berkas.destroy');
});


// ── Profil Pengguna (Global) ──────────────────────────────────────
Route::middleware('auth')->prefix('profil')->name('profil.')->group(function () {
    Route::get('/',         [ProfileController::class, 'edit'])->name('edit');
    Route::put('/',         [ProfileController::class, 'update'])->name('update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
});
