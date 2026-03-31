<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Expense;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\BudgetAlertMail;
use App\Services\GoogleCalendarService;

class AdminController extends Controller
{
    public function dashboard(Request $request)
{
    $users = User::all();
    $initialUserId = auth()->id(); // the logged‑in admin

    return view('admin.dashboard', compact('users', 'initialUserId'));
}

    public function userDashboardPartial(User $user, GoogleCalendarService $calendarService)
{
    $expenses = $user->expenses()->orderBy('expense_date', 'desc')->get();
    $totalExpenses = $user->getTotalExpenses();
    $remainingBudget = $user->getRemainingBudget();
    $budgetPercentage = $user->getBudgetPercentage();
    $cooldownDays = $user->getBudgetChangeCooldownDays();

    $categoryTotals = $user->expenses()
        ->select('category', DB::raw('SUM(amount) as total'))
        ->groupBy('category')
        ->pluck('total', 'category')
        ->toArray();

    $chartData = [
        'Food' => $categoryTotals['Food'] ?? 0,
        'Transport' => $categoryTotals['Transport'] ?? 0,
        'Utilities' => $categoryTotals['Utilities'] ?? 0,
        'Entertainment' => $categoryTotals['Entertainment'] ?? 0,
        'Others' => $categoryTotals['Others'] ?? 0,
    ];

    $events = [];
    if ($user->google_calendar_token) {
        $events = $calendarService->getUserCalendarEvents($user);
    }

    $isAdminView = true;

    return view('expenses.partials.dashboard', compact('user', 'expenses', 'totalExpenses', 'remainingBudget', 'budgetPercentage', 'cooldownDays', 'chartData', 'events', 'isAdminView'));
}

// Return logs as JSON
public function logsJson(Request $request)
{
    $logs = ActivityLog::query();
    if ($request->user_id && $request->user_id !== 'all') {
        $logs->where('user_id', $request->user_id);
    }
    if ($request->date) {
        $logs->whereDate('created_at', $request->date);
    }
    $logs = $logs->orderBy('created_at', 'desc')->get();
    return response()->json($logs);
}

    public function viewUser(User $user)
    {
        $expenses = $user->expenses()->orderBy('expense_date', 'desc')->get();
        $totalExpenses = $user->getTotalExpenses();
        $remainingBudget = $user->getRemainingBudget();
        $budgetPercentage = $user->getBudgetPercentage();

        $categoryTotals = $user->expenses()
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $chartData = [
            'Food' => $categoryTotals['Food'] ?? 0,
            'Transport' => $categoryTotals['Transport'] ?? 0,
            'Utilities' => $categoryTotals['Utilities'] ?? 0,
            'Entertainment' => $categoryTotals['Entertainment'] ?? 0,
            'Others' => $categoryTotals['Others'] ?? 0,
        ];

        return view('admin.user-view', compact('user', 'expenses', 'totalExpenses', 'remainingBudget', 'budgetPercentage', 'chartData'));
    }

    public function logs(Request $request)
    {
        $logs = ActivityLog::query();

        if ($request->user_id && $request->user_id !== 'all') {
            $logs->where('user_id', $request->user_id);
        }

        if ($request->date) {
            $logs->whereDate('created_at', $request->date);
        }

        $logs = $logs->orderBy('created_at', 'desc')->paginate(50);
        $users = User::all();

        return view('admin.logs', compact('logs', 'users'));
    }

    public function downloadUserPdf(User $user)
    {
        $expenses = $user->expenses()->orderBy('expense_date', 'desc')->get();

        $pdf = Pdf::loadView('pdf.user-report', compact('user', 'expenses'));
        return $pdf->download($user->name . "_expenses.pdf");
    }

    public function downloadAllUsersPdf()
    {
        $users = User::with('expenses')->get();

        $pdf = Pdf::loadView('pdf.all-users-report', compact('users'));
        return $pdf->download("all_users_expenses.pdf");
    }

    public function downloadLogsPdf(Request $request)
    {
        $logs = ActivityLog::query();

        if ($request->user_id && $request->user_id !== 'all') {
            $logs->where('user_id', $request->user_id);
        }

        if ($request->date) {
            $logs->whereDate('created_at', $request->date);
        }

        $logs = $logs->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.logs-report', compact('logs'));
        return $pdf->download("system_logs.pdf");
    }

    public function updateUserBudget(Request $request, User $user)
    {
        $request->validate([
            'budget_limit' => 'required|numeric|min:1',
        ]);

        $oldLimit = $user->budget_limit;
        $user->budget_limit = $request->budget_limit;
        $user->last_budget_change = now();
        $user->save();

        ActivityLog::log(
            auth()->id(),
            auth()->user()->name,
            'ADMIN_BUDGET_CHANGE',
            "Admin changed {$user->name}'s budget from ₱" . number_format($oldLimit, 2) . " to ₱" . number_format($request->budget_limit, 2)
        );

        return redirect()->route('admin.user.view', $user)->with('success', 'User budget updated successfully!');
    }

    public function editUser(User $user)
    {
        return view('admin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin',
            'budget_limit' => 'required|numeric|min:1',
        ]);

        $user->update($request->only('name', 'email', 'role', 'budget_limit'));

        ActivityLog::log(
            auth()->id(),
            auth()->user()->name,
            'ADMIN_USER_UPDATE',
            "Admin updated user {$user->name}: " . json_encode($request->only('name', 'email', 'role', 'budget_limit'))
        );

        return redirect()->route('admin.user.view', $user)->with('success', 'User updated successfully.');
    }

    public function monthlyExpenses(Request $request, User $user)
    {
        $month = $request->query('month', date('Y-m'));
        $expenses = $user->expenses()
            ->whereYear('expense_date', substr($month, 0, 4))
            ->whereMonth('expense_date', substr($month, 5, 2))
            ->orderBy('expense_date', 'desc')
            ->get();

        return response()->json($expenses);
    }

    public function downloadUserMonthlyPdf(Request $request, User $user)
    {
        $request->validate(['month' => 'required|date_format:Y-m']);
        $expenses = $user->expenses()
            ->whereYear('expense_date', substr($request->month, 0, 4))
            ->whereMonth('expense_date', substr($request->month, 5, 2))
            ->orderBy('expense_date', 'desc')
            ->get();

        $total = $expenses->sum('amount');
        $pdf = Pdf::loadView('pdf.monthly-report', [
            'user' => $user,
            'expenses' => $expenses,
            'total' => $total,
            'month' => $request->month
        ]);
        return $pdf->download($user->name . "_" . $request->month . "_expenses.pdf");
    }

    public function testMail()
    {
        $user = auth()->user();
        $percentage = 85;
        $total = 8500;
        $limit = 10000;
        Mail::to($user->email)->send(new BudgetAlertMail($user, $percentage, $total, $limit));
        return back()->with('success', 'Test email sent!');
    }
}
