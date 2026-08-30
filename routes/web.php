<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/locale/{locale}',
    [LocaleController::class, 'update']
)->name('locale.update');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get(
        '/login',
        [LoginController::class, 'create']
    )->name('login');

    Route::post(
        '/login',
        [LoginController::class, 'store']
    )->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    Route::get('/search', [SearchController::class, 'index'])
        ->name('search.index');


    Route::get('/calendar', [CalendarController::class, 'index'])
        ->name('calendar.index');

    Route::get('/boards', [BoardController::class, 'index'])
        ->name('boards.index');

    Route::patch('/boards/tasks/{task}/status', [BoardController::class, 'updateTaskStatus'])
        ->name('boards.tasks.status');

    Route::patch('/boards/tickets/{ticket}/status', [BoardController::class, 'updateTicketStatus'])
        ->name('boards.tickets.status');

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/export/csv', [ReportController::class, 'csv'])
        ->name('reports.csv');

    Route::get('/reports/export/excel', [ReportController::class, 'excel'])
        ->name('reports.excel');

    Route::get('/reports/export/pdf', [ReportController::class, 'pdf'])
        ->name('reports.pdf');

    Route::get('/reports/print', [ReportController::class, 'print'])
        ->name('reports.print');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])
        ->name('notifications.open');

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');

    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])
        ->name('notifications.read-all');

    Route::post('/collaboration/{type}/{id}/comments', [CommentController::class, 'store'])
        ->name('comments.store');

    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    Route::post('/collaboration/{type}/{id}/attachments', [AttachmentController::class, 'store'])
        ->name('attachments.store');

    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');

    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])
        ->name('attachments.destroy');

    Route::get('/activity', [AuditLogController::class, 'index'])
        ->name('activity.index');

    Route::get('/trash', [TrashController::class, 'index'])
        ->name('trash.index');

    Route::patch('/trash/{type}/{id}/restore', [TrashController::class, 'restore'])
        ->name('trash.restore');

    Route::delete('/trash/{type}/{id}', [TrashController::class, 'destroy'])
        ->name('trash.destroy');

    Route::resource(
        'companies',
        CompanyController::class
    );

    Route::resource(
        'contacts',
        ContactController::class
    );

    Route::resource(
        'projects',
        ProjectController::class
    );

    Route::patch(
        '/tasks/{task}/complete',
        [TaskController::class, 'complete']
    )->name('tasks.complete');

    Route::patch(
        '/tasks/{task}/reopen',
        [TaskController::class, 'reopen']
    )->name('tasks.reopen');

    Route::resource(
        'tasks',
        TaskController::class
    );

    Route::get(
        '/assets/{asset}/assignment',
        [AssetController::class, 'editAssignment']
    )->name('assets.assignment.edit');

    Route::put(
        '/assets/{asset}/assignment',
        [AssetController::class, 'updateAssignment']
    )->name('assets.assignment.update');

    Route::resource(
        'assets',
        AssetController::class
    );

    Route::patch(
        '/tickets/{ticket}/resolve',
        [TicketController::class, 'resolve']
    )->name('tickets.resolve');

    Route::patch(
        '/tickets/{ticket}/reopen',
        [TicketController::class, 'reopen']
    )->name('tickets.reopen');

    Route::resource(
        'tickets',
        TicketController::class
    );

    Route::resource(
        'users',
        UserController::class
    );

    Route::resource(
        'roles',
        RoleController::class
    );

    Route::post(
        '/logout',
        [LoginController::class, 'destroy']
    )->name('logout');
});
