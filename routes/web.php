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

    // Monthly data endpoints
    Route::get('/expenses/monthly-data', [ExpenseController::class, 'getMonthlyData'])->name('expenses.monthly.data');
    Route::get('/expenses/monthly-pdf', [ExpenseController::class, 'downloadMonthlyPdf'])->name('expenses.download.monthly');

    // Google Calendar routes
    Route::prefix('google')->name('google.')->group(function () {
        Route::get('auth', [GoogleCalendarController::class, 'redirectToGoogle'])->name('auth');
        Route::get('callback', [GoogleCalendarController::class, 'handleGoogleCallback'])->name('callback');
        Route::get('calendar', [GoogleCalendarController::class, 'viewCalendar'])->name('calendar');
        Route::get('disconnect', [GoogleCalendarController::class, 'disconnect'])->name('disconnect');
    });

    // Admin routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/user/{user}/dashboard', [AdminController::class, 'userDashboardPartial'])->name('user.dashboard');

        // Admin monthly endpoints
        Route::get('/users/{user}/monthly-data', [AdminController::class, 'monthlyExpenses'])->name('user.monthly.data');
        Route::get('/users/{user}/monthly-pdf', [AdminController::class, 'downloadUserMonthlyPdf'])->name('user.download.monthly.pdf');
        Route::get('/users/{user}/download-pdf', [AdminController::class, 'downloadUserPdf'])->name('user.download.pdf');
        Route::put('/users/{user}/budget', [AdminController::class, 'updateUserBudget'])->name('user.budget.update');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('user.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('user.update');

        // Logs
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
        Route::get('/logs/json', [AdminController::class, 'logsJson'])->name('logs.json');
        Route::get('/download-all-users-pdf', [AdminController::class, 'downloadAllUsersPdf'])->name('download.all.users');
        Route::get('/download-logs-pdf', [AdminController::class, 'downloadLogsPdf'])->name('download.logs');
    });
});

require __DIR__.'/auth.php';
