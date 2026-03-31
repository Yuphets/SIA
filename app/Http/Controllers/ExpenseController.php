<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Expense;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;      // <-- added
use App\Mail\BudgetAlertMail;
use App\Services\GoogleCalendarService;



class ExpenseController extends Controller
{
    public function index(GoogleCalendarService $calendarService)
{
    $user = auth()->user();
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

    // Fetch Google Calendar events if connected
    $events = [];
    if ($user->google_calendar_token) {
        $events = $calendarService->getUserCalendarEvents($user);
    }

    return view('expenses.index', compact(
        'expenses', 'totalExpenses', 'remainingBudget',
        'budgetPercentage', 'cooldownDays', 'chartData', 'user', 'events'
    ));
}

    public function store(Request $request)
    {
        $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|string',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
        ]);

        /** @var User $user */
        $user = auth()->user();
        $expense = Expense::create([
            'user_id' => $user->id,
            'expense_date' => $request->expense_date,
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
        ]);

        ActivityLog::log(
            $user->id,
            $user->name,
            'ADD_EXPENSE',
            "Added expense: {$request->expense_date} - {$request->category} - {$request->description} - ₱" . number_format($request->amount, 2)
        );

        $this->checkBudgetAlert($user);

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully!');
    }

    public function destroy(Expense $expense)
    {
        /** @var User $user */
        $user = auth()->user();
        if ($expense->user_id !== auth()->id()) {
    return redirect()->route('expenses.index')->with('error', 'Unauthorized action.');
}

        ActivityLog::log(
            $user->id,
            $user->name,
            'DELETE_EXPENSE',
            "Deleted expense: {$expense->expense_date} - {$expense->category} - {$expense->description} - ₱" . number_format($expense->amount, 2)
        );

        $expense->delete();

        $this->checkBudgetAlert($user);

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully!');
    }

    public function updateBudget(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $request->validate([
            'budget_limit' => 'required|numeric|min:1',
        ]);

        if (!$user->canChangeBudget() && !$user->isAdmin()) {
            return redirect()->route('expenses.index')->with('error',
                "You cannot change your budget for another {$user->getBudgetChangeCooldownDays()} day(s). Only admin can override."
            );
        }

        $oldLimit = $user->budget_limit;
        $user->budget_limit = $request->budget_limit;
        $user->last_budget_change = now();
        $user->save();

        ActivityLog::log(
            $user->id,
            $user->name,
            'BUDGET_CHANGE',
            "Changed budget from ₱" . number_format($oldLimit, 2) . " to ₱" . number_format($request->budget_limit, 2)
        );

        $this->checkBudgetAlert($user);

        return redirect()->route('expenses.index')->with('success',
            "Budget updated successfully! You cannot change it again for 7 days."
        );
    }

    public function downloadPdf()
    {
        /** @var User $user */
        $user = auth()->user();
        $expenses = $user->expenses()->orderBy('expense_date', 'desc')->get();

        $pdf = Pdf::loadView('pdf.expense-report', compact('user', 'expenses'));
        return $pdf->download($user->name . "_expenses.pdf");
    }

    public function downloadMonthlyPdf(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        /** @var User $user */
        $user = auth()->user();
        $expenses = $user->expenses()
            ->whereYear('expense_date', substr($request->month, 0, 4))
            ->whereMonth('expense_date', substr($request->month, 5, 2))
            ->orderBy('expense_date', 'desc')
            ->get();

        $total = $expenses->sum('amount');
        $month = $request->month;

        $pdf = Pdf::loadView('pdf.monthly-report', compact('user', 'expenses', 'total', 'month'));
        return $pdf->download($user->name . "_" . $request->month . "_expenses.pdf");
    }

   private function checkBudgetAlert($user)
{
    $percentage = $user->getBudgetPercentage();
    $totalExpenses = $user->getTotalExpenses();
    $budgetLimit = $user->budget_limit;

    if ($percentage >= 80) {
        // Send email via Mailtrap
        Mail::to($user->email)->send(new BudgetAlertMail($user, $percentage, $totalExpenses, $budgetLimit));

        // Flash alert to session (popup)
        session()->flash('budget_alert', [
            'type' => $percentage >= 100 ? 'danger' : 'warning',
            'title' => $percentage >= 100 ? '⚠️ BUDGET EXCEEDED!' : '⚠️ BUDGET WARNING!',
            'message' => $percentage >= 100
                ? "You have exceeded your budget by ₱" . number_format($totalExpenses - $budgetLimit, 2) . ". Please reduce expenses immediately."
                : "You have used " . number_format($percentage, 1) . "% of your budget (₱" . number_format($totalExpenses, 2) . " / ₱" . number_format($budgetLimit, 2) . "). Consider adjusting your spending.",
            'percentage' => $percentage
        ]);
    }
}
}
