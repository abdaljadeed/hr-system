<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{employee}/files', [EmployeeController::class, 'uploadFile'])->name('employees.files.store');
    Route::delete('employees/{employee}/files/{file}', [EmployeeController::class, 'destroyFile'])->name('employees.files.destroy');
    Route::get('employees/{employee}/files/{file}/download', [EmployeeController::class, 'downloadFile'])->name('employees.files.download');

    Route::resource('departments', DepartmentController::class)->except(['show']);

    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
        Route::get('/{employee}', [AttendanceController::class, 'show'])->name('show');
        Route::patch('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        Route::post('/{employee}/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
        Route::post('/{employee}/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
    });

    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
        Route::get('/{leave}', [LeaveRequestController::class, 'show'])->name('show');
        Route::post('/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('approve');
        Route::post('/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('reject');
        Route::post('/{leave}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::post('/', [PayrollController::class, 'store'])->name('store');
        Route::post('/bulk', [PayrollController::class, 'storeBulk'])->name('store-bulk');
        Route::get('/{payroll}', [PayrollController::class, 'show'])->name('show');
        Route::post('/{payroll}/items', [PayrollController::class, 'addItem'])->name('items.store');
        Route::delete('/{payroll}/items/{item}', [PayrollController::class, 'removeItem'])->name('items.destroy');
        Route::post('/{payroll}/finalize', [PayrollController::class, 'finalize'])->name('finalize');
        Route::post('/{payroll}/paid', [PayrollController::class, 'markPaid'])->name('paid');
        Route::get('/{payroll}/pdf', [PayrollController::class, 'downloadPdf'])->name('pdf');
    });

    Route::get('/activity-log', [ActivityLogController::class, 'index'])
        ->middleware('role:Admin|HR Manager')
        ->name('activity.index');

    Route::post('announcements/{announcement}/dismiss', [AnnouncementController::class, 'dismiss'])->name('announcements.dismiss');

    Route::middleware('permission:announcements.manage')->group(function () {
        Route::resource('announcements', AnnouncementController::class)->except(['show']);
    });

    Route::middleware('role:Admin|HR Manager')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/preview', [ReportController::class, 'preview'])->name('preview');
        Route::post('/excel', [ReportController::class, 'exportExcel'])->name('excel');
        Route::post('/pdf', [ReportController::class, 'exportPdf'])->name('pdf');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/create', [TaskController::class, 'create'])->name('create');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
        Route::post('/{task}/start', [TaskController::class, 'start'])->name('start');
        Route::post('/{task}/submit', [TaskController::class, 'submit'])->name('submit');
        Route::post('/{task}/approve', [TaskController::class, 'approve'])->name('approve');
        Route::post('/{task}/reject', [TaskController::class, 'reject'])->name('reject');
        Route::post('/{task}/reassign', [TaskController::class, 'reassign'])->name('reassign');
        Route::post('/{task}/comments', [TaskController::class, 'comment'])->name('comments.store');
    });
});

require __DIR__.'/auth.php';
