<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MaterialController;
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
Route::post('/verify-upload', [AuthController::class, 'verifyUpload'])->name('verify.upload');
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
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Routes
Route::middleware('auth', 'force.password.change')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(Auth::user()->role.'.dashboard');
    })->name('dashboard');

    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

    // Force password change routes (accessible even with force_password_change flag)
    Route::get('/password/change', function () {
        return view('auth.change-password');
    })->name('password.change');
    Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('password.update');

    Route::middleware('role:admin')->group(function () {
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

    Route::prefix('student')->middleware('role:student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
        Route::get('/announcements', [AnnouncementController::class, 'studentIndex'])->name('announcements.index');
        Route::get('/enrollment', [StudentController::class, 'enrollment'])->name('enrollment');
        Route::post('/enrollment', [StudentController::class, 'storeEnrollment'])->name('enrollment.store');
        Route::post('/enrollment/{moduleRecord}/drop', [StudentController::class, 'dropEnrollment'])->name('enrollment.drop');
        Route::post('/modules/records', [StudentController::class, 'storeModuleRecord'])->name('modules.records.store');
        Route::delete('/modules/records/{moduleRecord}', [StudentController::class, 'deleteModuleRecord'])->name('modules.records.destroy');
        Route::get('/grades', [StudentController::class, 'grades'])->name('grades');
        Route::get('/classrooms', [ClassroomController::class, 'studentIndex'])->name('classrooms');
        Route::post('/classrooms/{classroom}/enroll', [ClassroomController::class, 'studentEnroll'])->middleware('classroom.active')->name('classrooms.enroll');
        Route::get('/attendance', [StudentController::class, 'attendance'])->name('attendance');
        Route::get('/documents', [StudentController::class, 'documents'])->name('documents');
        Route::get('/forum', [StudentController::class, 'forum'])->name('forum');
        Route::get('/schedule', [StudentController::class, 'schedule'])->name('schedule');
        Route::get('/notifications', [StudentController::class, 'notifications'])->name('notifications');
        Route::get('/settings', [StudentController::class, 'settings'])->name('settings');
    });

    Route::prefix('faculty')->middleware('role:faculty')->name('faculty.')->group(function () {
        Route::get('/dashboard', [FacultyController::class, 'dashboard'])->name('dashboard');
        Route::get('/announcements', [AnnouncementController::class, 'facultyIndex'])->name('announcements.index');
        Route::post('/announcements', [AnnouncementController::class, 'facultyStore'])->name('announcements.store');
        Route::get('/students', [FacultyController::class, 'students'])->name('students');
        Route::get('/students/{slug}', [FacultyController::class, 'subjectShow'])->name('students.show');
        Route::get('/student-details/{id}', [FacultyController::class, 'studentShow'])->name('student.details');
        Route::get('/grades', [FacultyController::class, 'grades'])->name('grades');
        Route::get('/grades/export-grades', [GradeController::class, 'export'])->name('grades.export.csv');
        Route::post('/grades/store', [GradeController::class, 'store'])->name('grades.save');
        Route::get('/grades/export', [FacultyController::class, 'exportAttendanceRecords'])->name('grades.export');
        Route::get('/grades/load-today-attendance', [FacultyController::class, 'loadTodayAttendance'])->name('grades.load-today-attendance');
        Route::post('/grades/records', [FacultyController::class, 'storeAttendanceRecord'])->middleware('classroom.active')->name('grades.records.store');
        Route::patch('/grades/records/{attendanceRecord}', [FacultyController::class, 'updateAttendanceRecord'])->name('grades.records.update');
        Route::get('/enrollments', [FacultyController::class, 'enrollments'])->name('enrollments');
        Route::patch('/enrollments/{moduleRecord}/approve', [FacultyController::class, 'approveEnrollment'])->name('enrollments.approve');
        Route::patch('/enrollments/{moduleRecord}/section', [FacultyController::class, 'assignSection'])->name('enrollments.section');
        Route::get('/classrooms', [ClassroomController::class, 'facultyIndex'])->name('classrooms');
        Route::get('/classrooms/create', [ClassroomController::class, 'facultyCreate'])->name('classrooms.create');
        Route::post('/classrooms', [ClassroomController::class, 'facultyStore'])->name('classrooms.store');
        Route::get('/classrooms/{classroom}/edit', [ClassroomController::class, 'facultyEdit'])->name('classrooms.edit');
        Route::put('/classrooms/{classroom}', [ClassroomController::class, 'facultyUpdate'])->name('classrooms.update');
        Route::get('/classrooms/{classroom}', [ClassroomController::class, 'facultyShow'])->name('classrooms.show');
        Route::get('/forum', [FacultyController::class, 'forum'])->name('forum');
        Route::get('/profile', [FacultyController::class, 'profile'])->name('profile');
        Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
        // Export classroom students (faculty)
        Route::get('/classrooms/{classroom}/export', [ClassroomController::class, 'adminExport'])->name('faculty.classrooms.export');
        Route::get('/schedule', [FacultyController::class, 'schedule'])->name('schedule');
    });

    // NOTE: Removed duplicate global route `grades.export.csv` to prefer
    // canonical `faculty.grades.export.csv` (the route inside the faculty group).

    Route::prefix('admin')->middleware('role:admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/announcements', [AnnouncementController::class, 'manage'])->name('announcements.index');
        Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance');
        Route::get('/attendance/export', [AdminController::class, 'exportAttendance'])->name('attendance.export');
        Route::get('/grades', [AdminController::class, 'grades'])->name('grades');
        Route::patch('/grades/{moduleRecord}/verify', [AdminController::class, 'verifyGrade'])->name('grades.verify');
        Route::patch('/grades/{moduleRecord}/update', [AdminController::class, 'updateGrade'])->name('grades.update');
        Route::get('/grades/generator', [AdminController::class, 'exportGrades'])->name('grades.export');
        Route::get('/enrollments', [AdminController::class, 'enrollments'])->name('enrollments');
        Route::patch('/enrollments/{moduleRecord}/approve', [AdminController::class, 'approveEnrollment'])->name('enrollments.approve');
        Route::patch('/enrollments/{moduleRecord}/section', [AdminController::class, 'assignSection'])->name('enrollments.section');
        Route::patch('/enrollments/{moduleRecord}/encode', [AdminController::class, 'encodeCourse'])->name('enrollments.encode');
        Route::get('/classrooms', [ClassroomController::class, 'adminIndex'])->name('classrooms');
        Route::get('/classrooms/{classroom}', [ClassroomController::class, 'adminShow'])->name('classrooms.show');
        Route::patch('/classrooms/{classroom}/status', [ClassroomController::class, 'adminToggleStatus'])->name('classrooms.status');
        Route::post('/classrooms/{classroom}/assign-faculty', [ClassroomController::class, 'adminAssignFaculty'])->name('classrooms.assign-faculty');
        Route::get('/classrooms/{classroom}/export', [ClassroomController::class, 'adminExport'])->name('classrooms.export');
        Route::get('/documents', [AdminController::class, 'documents'])->name('documents');
        Route::patch('/documents/{documentRequest}', [AdminController::class, 'updateDocument'])->name('documents.update');
        Route::get('/forum', [AdminController::class, 'forum'])->name('forum');
        Route::get('/audit-trail', [AdminController::class, 'auditTrail'])->name('audit-trail');
        Route::get('/system-monitoring', [AdminController::class, 'systemMonitoring'])->name('system-monitoring');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/faculty', [AdminController::class, 'facultyDirectory'])->name('faculty');
        Route::get('/faculty/{user}/show', [AdminController::class, 'facultyShow'])->name('faculty.show');
        Route::patch('/faculty/{user}/toggle-status', [AdminController::class, 'toggleFacultyStatus'])->name('faculty.toggle-status');
        Route::patch('/users/{user}/activate', [AdminController::class, 'activateUser'])->name('users.activate');
        // Bulk import routes
        Route::get('/users/template/download', [AdminController::class, 'downloadStudentTemplate'])->name('users.template.download');
        Route::post('/users/import', [AdminController::class, 'importStudents'])->name('users.import');
        Route::get('/users/{user}/show', [AdminController::class, 'showStudent'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminController::class, 'editStudent'])->name('users.edit');
        Route::post('/users/{user}/edit', [AdminController::class, 'editStudent']);
        Route::patch('/users/{user}/toggle-status', [AdminController::class, 'toggleStudentStatus'])->name('users.toggle-status');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        Route::post('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
        // Maintenance & Backup
        Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance');
        Route::post('/maintenance/backup', [MaintenanceController::class, 'backup'])->name('maintenance.backup');
        Route::get('/maintenance/backup/{filename}/download', [MaintenanceController::class, 'download'])->name('maintenance.backup.download');
        Route::delete('/maintenance/backup', [MaintenanceController::class, 'deleteBackup'])->name('maintenance.backup.delete');
        Route::post('/maintenance/restore', [MaintenanceController::class, 'restore'])->name('maintenance.restore');
        Route::post('/maintenance/toggle', [MaintenanceController::class, 'toggleMaintenance'])->name('maintenance.toggle');
        Route::post('/maintenance/schedule', [MaintenanceController::class, 'updateSchedule'])->name('maintenance.schedule');
    });
});
