<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CourseregController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KehadiranController;
use App\Http\Controllers\LaporanSIGController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PelajarController;
use App\Http\Controllers\penilaianController;
use App\Http\Controllers\SemakanMarkahController;
use App\Http\Controllers\semakanTugasanController;
use App\Http\Controllers\SigCoordinatorController;
use App\Http\Controllers\StudentRegistrationController;
use App\Http\Controllers\subkriteriaController;
use App\Http\Controllers\SesiKursusController;
use App\Http\Controllers\tugasanController;
use App\Http\Controllers\tugasanPelajarController;

// Login & Auth
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [LoginController::class, 'authenticate']);

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Reset Password
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'updatePassword'])->name('password.update');

// Dashboard Utama
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:1,2,3,4'])
    ->name('dashboard');

// Pentadbir (Role 1)
Route::middleware(['auth', 'role:1'])->group(function () {
    // Pengurusan Sesi Kursus (Tetapan Semester)
    Route::get('/sesiKursus', [SesiKursusController::class, 'index'])->name('sesiKursus.index');
    Route::post('/sesiKursus', [SesiKursusController::class, 'store'])->name('sesiKursus.store');
    Route::patch('/sesiKursus/{id}/aktif', [SesiKursusController::class, 'aktif'])->name('sesiKursus.aktif');

    // Legacy: Pengurusan Kursus (kept for backward compatibility)
    Route::get('/coursereg', [CourseregController::class, 'index'])->name('coursereg');
    Route::post('/coursereg', [CourseregController::class, 'store'])->name('coursereg.store');

    // Pengurusan Penyelaras SIG
    Route::get('/penyelarasSigRegistration', [SigCoordinatorController::class, 'index'])->name('penyelarasSigRegistration');
    Route::post('/penyelarasSigRegistration/store', [SigCoordinatorController::class, 'store'])->name('penyelarasSigRegistration.store');
    Route::put('/penyelarasSigRegistration/update', [SigCoordinatorController::class, 'update'])->name('penyelarasSigRegistration.update');
    Route::delete('/penyelarasSigRegistration/delete', [SigCoordinatorController::class, 'destroy'])->name('penyelarasSigRegistration.delete');

    // Laporan
    Route::get('/laporanSIG', [LaporanSIGController::class, 'index'])->name('laporanSIG');
    Route::get('/laporanSIG/export/{sigId}', [LaporanSIGController::class, 'exportSIG'])->name('laporanSIG.export');
    Route::get('/laporanSIG/view/{sigId}', [LaporanSIGController::class, 'viewSIG'])->name('laporanSIG.view');
});

// Penyelaras (Role 2)
Route::middleware(['auth', 'role:2'])->group(function () {
    // Pendaftaran Pelajar
    Route::get('/registration', [StudentRegistrationController::class, 'index'])->name('registration');
    Route::post('/registration/fetch-student', [StudentRegistrationController::class, 'fetchStudent'])->name('registration.fetch');
    Route::post('/registration/individual', [StudentRegistrationController::class, 'registerIndividual'])->name('registration.individual');
    Route::post('/registration/bulk', [StudentRegistrationController::class, 'registerBulk'])->name('registration.bulk');
    
    // Legacy Routes
    Route::get('/studentRegister', [PelajarController::class, 'index'])->name('studentRegister');
    Route::get('/register', [PelajarController::class, 'index'])->name('register');
    Route::post('/register', [PelajarController::class, 'store'])->name('student.register');
    Route::get('/api/pelajar/{nombor_matrik}', [PelajarController::class, 'cari']);

    // Rubrik & Subkriteria
    Route::get('/subkriteria', [subkriteriaController::class, 'index'])->name('subkriteria');
    Route::post('/subkriteria/store', [subkriteriaController::class, 'store'])->name('subkriteria.store');
    Route::post('/subkriteria/create', [subkriteriaController::class, 'createSubkriteria'])->name('subkriteria.create');

    // Pengurusan Tugasan
    Route::get('/tugasan', [tugasanController::class, 'index'])->name('tugasan');
    Route::post('/tugasan', [tugasanController::class, 'store'])->name('tugasan.store');
    Route::put('/tugasan/{id}', [tugasanController::class, 'update'])->name('tugasan.update');
    Route::delete('/tugasan/{id}', [tugasanController::class, 'destroy'])->name('tugasan.delete');
    Route::post('/tugasan/toggle-publish/{id}', [tugasanController::class, 'togglePublish'])->name('tugasan.togglePublish');

    // Semakan & Markah
    Route::get('/semakanTugasan/{id}', [semakanTugasanController::class, 'show'])->name('semakanTugasan.show');
    Route::post('/semakanTugasan/{id}/saveMarks', [semakanTugasanController::class, 'saveMarks'])->name('semakanTugasan.saveMarks');

    // Penilaian Akhir
    Route::get('/penilaian', [penilaianController::class, 'index'])->name('penilaian');
    Route::get('/penilaian/markah/{nomat}', [penilaianController::class, 'markah'])->name('penilaian.markah');
    Route::post('/penilaian/publish', [penilaianController::class, 'updatePublishStatus'])->name('penilaian.publish');
    Route::get('/penilaian/export', [penilaianController::class, 'exportCSV'])->name('penilaian.export');
    Route::post('/penilaian/simpan/{nomat}', [penilaianController::class, 'simpan'])->name('penilaian.simpan');
});

// Pelajar (Role 3) 
Route::middleware(['auth', 'role:3'])->group(function () {
    Route::get('/tugasanPelajar', [tugasanPelajarController::class, 'index'])->name('tugasanPelajar');
    Route::post('/tugasanPelajar', [tugasanPelajarController::class, 'store'])->name('tugasanPelajar.store');
    Route::delete('/tugasanPelajar/{id}', [tugasanPelajarController::class, 'destroySubmission'])->name('tugasanPelajar.delete');
    Route::get('/semakanmarkah', [SemakanMarkahController::class, 'index'])->name('semakanmarkah');
});

//  Kehadiran (Shared)
Route::middleware(['auth'])->group(function () {
    // View & Rekod (Role 2 & 3)
    Route::middleware('role:2,3')->group(function () {
        Route::get('/kehadiran', [KehadiranController::class, 'index'])->name('kehadiran');
        Route::get('/kehadiran/rekod/{id}', [KehadiranController::class, 'rekodKehadiran'])->name('kehadiran.rekod');
        Route::post('/kehadiran/rekod/{id}', [KehadiranController::class, 'simpanKehadiran'])->name('kehadiran.simpan');
    });

    // Create Perjumpaan (MT - Role 3)
    Route::post('/kehadiran/perjumpaan', [KehadiranController::class, 'storePerjumpaan'])
        ->middleware('role:3')
        ->name('kehadiran.storePerjumpaan');
        
    // Sahkan & Export (Penyelaras - Role 2)
    Route::middleware('role:2')->group(function () {
        Route::post('/kehadiran/sahkan/{id}', [KehadiranController::class, 'sahkanKehadiran'])->name('kehadiran.sahkan');
        Route::get('/kehadiran/export', [KehadiranController::class, 'exportCSV'])->name('kehadiran.export');
    });
});