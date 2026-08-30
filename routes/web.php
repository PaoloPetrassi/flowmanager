<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
