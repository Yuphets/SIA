@extends('layouts.app')
@section('content')
    @include('expenses.partials.dashboard', [
        'user' => auth()->user(),
        'expenses' => $expenses,
        'totalExpenses' => $totalExpenses,
        'remainingBudget' => $remainingBudget,
        'budgetPercentage' => $budgetPercentage,
        'cooldownDays' => $cooldownDays,
        'chartData' => $chartData,
        'events' => $events,
        'isAdminView' => false
    ])
@endsection
