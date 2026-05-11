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
        Route::post('/documents', [StudentController::class, 'storeDocument'])->name('documents.store');
        Route::get('/forum', [StudentController::class, 'forum'])->name('forum');
        Route::post('/forum', [StudentController::class, 'storeForumThread'])->name('forum.store');
        Route::post('/forum/{forumThread}/reply', [StudentController::class, 'storeForumReply'])->name('forum.reply');
        Route::post('/forum/{forumThread}/report', [StudentController::class, 'reportForumThread'])->name('forum.report');
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

        // Classroom grading criteria management
        Route::post('/classrooms/{classroom}/grading-criteria', [FacultyController::class, 'storeGradingCriteria'])->name('classrooms.grading-criteria.store');
        Route::delete('/classrooms/{classroom}/grading-criteria/{criteria}', [FacultyController::class, 'deleteGradingCriteria'])->name('classrooms.grading-criteria.destroy');
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
        Route::get('/classrooms/create', [ClassroomController::class, 'adminCreate'])->name('classrooms.create');
        Route::post('/classrooms', [ClassroomController::class, 'adminStore'])->name('classrooms.store');
        Route::get('/classrooms/{classroom}', [ClassroomController::class, 'adminShow'])->name('classrooms.show');
        Route::patch('/classrooms/{classroom}/status', [ClassroomController::class, 'adminToggleStatus'])->name('classrooms.status');
        Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'adminDestroy'])->name('classrooms.destroy');
        Route::post('/classrooms/{classroom}/assign-faculty', [ClassroomController::class, 'adminAssignFaculty'])->name('classrooms.assign-faculty');
        Route::get('/classrooms/{classroom}/export', [ClassroomController::class, 'adminExport'])->name('classrooms.export');
        Route::get('/documents', [AdminController::class, 'documents'])->name('documents');
        Route::patch('/documents/{documentRequest}', [AdminController::class, 'updateDocument'])->name('documents.update');
        Route::delete('/documents/{documentRequest}', [AdminController::class, 'deleteDocument'])->name('documents.delete');
        Route::get('/forum', [AdminController::class, 'forum'])->name('forum');
        Route::get('/forum/{forumThread}', [AdminController::class, 'showForumThread'])->name('forum.show');
        Route::post('/forum/{forumThread}/toggle-hide', [AdminController::class, 'toggleHideForumThread'])->name('forum.toggleHide');
        Route::post('/forum/{forumThread}/flag', [AdminController::class, 'flagForumThread'])->name('forum.flag');
        Route::delete('/forum/{forumThread}', [AdminController::class, 'deleteForumThread'])->name('forum.delete');
        Route::get('/audit-trail', [AdminController::class, 'auditTrail'])->name('audit-trail');
        Route::get('/system-monitoring', [AdminController::class, 'systemMonitoring'])->name('system-monitoring');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/faculty', [AdminController::class, 'facultyDirectory'])->name('faculty');
        Route::get('/faculty/{user}/show', [AdminController::class, 'facultyShow'])->name('faculty.show');
        Route::patch('/faculty/{user}/toggle-status', [AdminController::class, 'toggleFacultyStatus'])->name('faculty.toggle-status');
        
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::patch('/users/{user}/edit', [AdminController::class, 'editUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::patch('/users/{user}/activate', [AdminController::class, 'toggleUserStatus'])->name('users.activate');
        
        // Bulk import routes
        Route::get('/users/template/student', [AdminController::class, 'downloadStudentTemplate'])->name('users.template.student');
        Route::get('/users/template/faculty', [AdminController::class, 'downloadFacultyTemplate'])->name('users.template.faculty');
        Route::get('/users/template/admin', [AdminController::class, 'downloadAdminTemplate'])->name('users.template.admin');
        Route::post('/users/import', [AdminController::class, 'importUsers'])->name('users.import');
        Route::patch('/users/{user}/toggle-status', [AdminController::class, 'toggleStudentStatus'])->name('users.toggle-status');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::patch('/settings/password', [AdminController::class, 'updatePassword'])->name('settings.password.update');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        Route::post('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
        // Maintenance & Backup
        Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance');
        Route::post('/maintenance/backup', [MaintenanceController::class, 'backup'])->name('maintenance.backup');
        Route::get('/maintenance/backup/{filename}/download', [MaintenanceController::class, 'download'])->name('maintenance.backup.download');
        Route::delete('/maintenance/backup', [MaintenanceController::class, 'deleteBackup'])->name('maintenance.backup.delete');
        Route::post('/maintenance/restore', [MaintenanceController::class, 'restore'])->name('maintenance.restore');
        Route::post('/maintenance/schedule', [MaintenanceController::class, 'updateSchedule'])->name('maintenance.schedule');
    });
});
