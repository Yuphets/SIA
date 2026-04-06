<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GoogleCalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard redirect
    Route::get('/dashboard', function () {
        return redirect()->route('expenses.index');
    })->name('dashboard');

    // Expense routes (user dashboard)
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::put('/expenses/budget', [ExpenseController::class, 'updateBudget'])->name('expenses.budget.update');
    Route::get('/expenses/download-pdf', [ExpenseController::class, 'downloadPdf'])->name('expenses.download.pdf');
    Route::get('/expenses/download-monthly-pdf', [ExpenseController::class, 'downloadMonthlyPdf'])->name('expenses.download.monthly');

    // Google Calendar routes
    Route::prefix('google')->name('google.')->group(function () {
        Route::get('auth', [GoogleCalendarController::class, 'redirectToGoogle'])->name('auth');
        Route::get('callback', [GoogleCalendarController::class, 'handleGoogleCallback'])->name('callback');
        Route::get('calendar', [GoogleCalendarController::class, 'viewCalendar'])->name('calendar');
    });

    // Admin routes (protected by admin middleware)
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/logs/json', [AdminController::class, 'logsJson'])->name('logs.json');
        Route::get('/user/{user}/dashboard', [AdminController::class, 'userDashboardPartial'])->name('user.dashboard');
        Route::get('/users/{user}', [AdminController::class, 'viewUser'])->name('user.view');
        Route::put('/users/{user}/budget', [AdminController::class, 'updateUserBudget'])->name('user.budget.update');
        Route::get('/users/{user}/download-pdf', [AdminController::class, 'downloadUserPdf'])->name('user.download.pdf');
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
        Route::get('/download-all-users-pdf', [AdminController::class, 'downloadAllUsersPdf'])->name('download.all.users');
        Route::get('/download-logs-pdf', [AdminController::class, 'downloadLogsPdf'])->name('download.logs');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('user.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('user.update');
        Route::get('/users/{user}/monthly-expenses', [AdminController::class, 'monthlyExpenses'])->name('user.monthly.expenses');
        Route::get('/users/{user}/monthly-pdf', [AdminController::class, 'downloadUserMonthlyPdf'])->name('user.download.monthly.pdf');
        Route::get('/users/{user}/download-pdf', [AdminController::class, 'downloadUserPdf'])->name('user.download.pdf');
    });
});

require __DIR__.'/auth.php';
