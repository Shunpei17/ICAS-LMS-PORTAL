<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('home');
})->name('home');

// Auth Routes
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('login');
})->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/register', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('register');
})->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::get('/forgot-password/sent', [AuthController::class, 'showForgotPasswordSent'])->name('password.sent');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(Auth::user()->role.'.dashboard');
    })->name('dashboard');

    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

    Route::middleware('role:admin')->group(function () {
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

    Route::prefix('student')->middleware('role:student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/announcements', [AnnouncementController::class, 'studentIndex'])->name('announcements.index');
        Route::get('/enrollment', [StudentController::class, 'enrollment'])->name('enrollment');
        Route::post('/enrollment', [StudentController::class, 'storeEnrollment'])->name('enrollment.store');
        Route::post('/modules/records', [StudentController::class, 'storeModuleRecord'])->name('modules.records.store');
        Route::delete('/modules/records/{moduleRecord}', [StudentController::class, 'deleteModuleRecord'])->name('modules.records.destroy');
        Route::get('/grades', [StudentController::class, 'grades'])->name('grades');
        Route::get('/classrooms', [StudentController::class, 'classrooms'])->name('classrooms');
        Route::get('/documents', [StudentController::class, 'documents'])->name('documents');
        Route::get('/forum', [StudentController::class, 'forum'])->name('forum');
    });

    Route::prefix('faculty')->middleware('role:faculty')->name('faculty.')->group(function () {
        Route::get('/dashboard', [FacultyController::class, 'dashboard'])->name('dashboard');
        Route::get('/announcements', [AnnouncementController::class, 'facultyIndex'])->name('announcements.index');
        Route::get('/students', [FacultyController::class, 'students'])->name('students');
        Route::get('/grades', [FacultyController::class, 'grades'])->name('grades');
        Route::get('/grades/export', [FacultyController::class, 'exportAttendanceRecords'])->name('grades.export');
        Route::post('/grades/records', [FacultyController::class, 'storeAttendanceRecord'])->name('grades.records.store');
        Route::patch('/grades/records/{attendanceRecord}', [FacultyController::class, 'updateAttendanceRecord'])->name('grades.records.update');
    });

    Route::prefix('admin')->middleware('role:admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/announcements', [AnnouncementController::class, 'manage'])->name('announcements.index');
        Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance');
        Route::get('/grades', [AdminController::class, 'grades'])->name('grades');
        Route::get('/grades/generator', [AdminController::class, 'exportGrades'])->name('grades.export');
        Route::get('/classrooms', [AdminController::class, 'classrooms'])->name('classrooms');
        Route::get('/documents', [AdminController::class, 'documents'])->name('documents');
        Route::get('/forum', [AdminController::class, 'forum'])->name('forum');
    });
});
