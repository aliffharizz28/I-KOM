<?php

use App\Http\Controllers\CourseregController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PelajarController;
use App\Http\Controllers\StudentRegistrationController;
use App\Http\Controllers\SigCoordinatorController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\tugasanController;
use App\Http\Controllers\tugasanPelajarController;

Route::get('/', function () {
    return redirect('/login');
});

// Papar borang login
Route::get('/login', function () {
    return view('login');
})->name('login');

// Proses data bila tekan butang 'Log Masuk'
Route::post('/login', [LoginController::class, 'authenticate']);

// Route untuk dashboard (Semua Role Yang Sah)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'role:1,2,3,4'])->name('dashboard');

// Route untuk logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');


Route::get('/studentRegister', [PelajarController::class, 'index'])->middleware(['auth', 'role:2'])->name('studentRegister');

// Route untuk paparkan borang pendaftaran pelajar
Route::get('/register', [PelajarController::class, 'index'])->middleware(['auth', 'role:2'])->name('register');

// Route untuk simpan data pelajar (register student to SIG)
Route::post('/register', [PelajarController::class, 'store'])->middleware(['auth', 'role:2'])->name('student.register');

// API route untuk cari pelajar berdasarkan nombor matrik
Route::get('/api/pelajar/{nombor_matrik}', [PelajarController::class, 'cari'])->middleware(['auth', 'role:2']);

Route::get('/forgot-password',[ForgotPasswordController::class,'showLinkRequestForm'])->name('password.request');

Route::post('/forgot-password',[ForgotPasswordController::class,'sendResetLinkEmail'])->name('password.email');

Route::get('/reset-password/{token}',[ResetPasswordController::class,'showResetForm'])->name('password.reset');

Route::post('/reset-password',[ResetPasswordController::class,'updatePassword'])->name('password.update');

// New Registration Routes
Route::middleware(['auth', 'role:2'])->group(function () {
    Route::get('/registration', [StudentRegistrationController::class, 'index'])->name('registration');
    Route::post('/registration/fetch-student', [StudentRegistrationController::class, 'fetchStudent'])->name('registration.fetch');
    Route::post('/registration/individual', [StudentRegistrationController::class, 'registerIndividual'])->name('registration.individual');
    Route::post('/registration/bulk', [StudentRegistrationController::class, 'registerBulk'])->name('registration.bulk');
});

Route::get('/penyelarasSigRegistration', [SigCoordinatorController::class, 'index'])
    ->middleware(['auth', 'role:1'])
    ->name('penyelarasSigRegistration');
    
Route::post('/penyelarasSigRegistration/store', [SigCoordinatorController::class, 'store'])
    ->middleware(['auth', 'role:1'])
    ->name('penyelarasSigRegistration.store');

Route::put('/penyelarasSigRegistration/update', [SigCoordinatorController::class, 'update'])
    ->middleware(['auth', 'role:1'])
    ->name('penyelarasSigRegistration.update');

Route::delete('/penyelarasSigRegistration/delete', [SigCoordinatorController::class, 'destroy'])
    ->middleware(['auth', 'role:1'])
    ->name('penyelarasSigRegistration.delete');
    
Route::get('/coursereg', [CourseregController::class, 'index'])
    ->middleware(['auth', 'role:1'])
    ->name('coursereg');

Route::post('/coursereg', [CourseregController::class, 'store'])
    ->middleware(['auth', 'role:1'])
    ->name('coursereg.store');

Route::get('/tugasan', [tugasanController::class, 'index'])
    ->middleware(['auth', 'role:2'])
    ->name('tugasan');

Route::post('/tugasan', [tugasanController::class, 'store'])
    ->middleware(['auth', 'role:2'])
    ->name('tugasan.store');

Route::put('/tugasan/{id}', [tugasanController::class, 'update'])
    ->middleware(['auth', 'role:2'])
    ->name('tugasan.update');

Route::delete('/tugasan/{id}', [tugasanController::class, 'destroy'])
    ->name('tugasan.delete');

// Tugasan Pelajar (Role 3)
Route::get('/tugasanPelajar', [tugasanPelajarController::class, 'index'])
    ->middleware(['auth', 'role:3'])
    ->name('tugasanPelajar');

Route::post('/tugasanPelajar', [tugasanPelajarController::class, 'store'])
    ->middleware(['auth', 'role:3'])
    ->name('tugasanPelajar.store');
Route::get('/semakanTugasan', function () { return view('semakanTugasan'); });
