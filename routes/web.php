<?php

use App\Http\Controllers\Admin\CvAdminController;
use App\Http\Controllers\Admin\DocxExportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ResetPasswordRequiredController;
use App\Http\Controllers\Candidate\AiImportController;
use App\Http\Controllers\Candidate\CvController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $user = request()->user();
    if (!$user) {
        return redirect()->route('login');
    }
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('cv.edit');
});

Route::get('/dashboard', function () {
    $user = request()->user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($user->role === 'candidate') {
        return redirect()->route('cv.edit');
    }
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Reset password required (candidati con must_change_password=true)
Route::middleware(['auth'])->group(function () {
    Route::get('/reset-password-required', [ResetPasswordRequiredController::class, 'show'])
        ->name('password.change.required');
});

// Candidate routes
Route::middleware(['auth', 'verified', 'role:candidate', 'must.change.password'])->group(function () {
    Route::get('/cv/edit', [CvController::class, 'edit'])->name('cv.edit');
    Route::put('/cv', [CvController::class, 'update'])->name('cv.update');
    Route::post('/cv/projects', [CvController::class, 'storeProject'])->name('cv.projects.store');
    Route::put('/cv/projects/{project}', [CvController::class, 'updateProject'])->name('cv.projects.update');
    Route::delete('/cv/projects/{project}', [CvController::class, 'destroyProject'])->name('cv.projects.destroy');
    Route::post('/cv/ai-import/text', [AiImportController::class, 'fromText'])->name('cv.ai-import.text');
    Route::post('/cv/ai-import/pdf',  [AiImportController::class, 'fromPdf'])->name('cv.ai-import.pdf');
});

// Admin routes
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [CvAdminController::class, 'index'])->name('admin.dashboard');
    Route::patch('/admin/cvs/{cv}/lock', [CvAdminController::class, 'lock'])->name('admin.cvs.lock');
    Route::patch('/admin/cvs/{cv}/unlock', [CvAdminController::class, 'unlock'])->name('admin.cvs.unlock');
    Route::patch('/admin/cvs/{cv}/archive', [CvAdminController::class, 'archive'])->name('admin.cvs.archive');
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
    Route::patch('/admin/users/{user}/suspend', [UserController::class, 'suspend'])->name('admin.users.suspend');
    Route::patch('/admin/users/{user}/unsuspend', [UserController::class, 'unsuspend'])->name('admin.users.unsuspend');
    Route::get('/admin/cvs/{cv}/export', [DocxExportController::class, 'export'])->name('admin.cvs.export');
    Route::post('/admin/users/invite', [UserController::class, 'invite'])->name('admin.users.invite');
    Route::post('/admin/users/invite-admin', [UserController::class, 'inviteAdmin'])->name('admin.users.invite-admin');
    Route::patch('/admin/users/{user}/reset-ai-import', [UserController::class, 'resetAiImport'])->name('admin.users.reset-ai-import');
});

require __DIR__.'/auth.php';
