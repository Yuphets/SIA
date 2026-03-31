@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">← Back to Dashboard</a>
</div>

<div class="card">
    <h2 class="text-xl font-bold mb-4">System Activity Logs (Digital Footprint)</h2>

    <form method="GET" action="{{ route('admin.logs') }}" class="flex flex-wrap gap-4 mb-6">
        <select name="user_id" class="border rounded-lg px-3 py-1">
            <option value="all">All Users</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                    {{ $u->name }}
                </option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ request('date') }}" class="border rounded-lg px-3 py-1">
        <button type="submit" class="btn-outline">Filter</button>
        <a href="{{ route('admin.logs') }}" class="btn-outline">Reset</a>
        <a href="{{ route('admin.download.logs', request()->all()) }}" class="btn-primary">Download Logs (PDF)</a>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Timestamp</th>
                    <th class="text-left py-2">User</th>
                    <th class="text-left py-2">Action</th>
                    <th class="text-left py-2">Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr class="border-b">
                    <td class="py-2 text-sm">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td class="py-2">{{ $log->username }}</td>
                    <td class="py-2">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $log->action == 'ADD_EXPENSE' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $log->action == 'DELETE_EXPENSE' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $log->action == 'BUDGET_CHANGE' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $log->action == 'ADMIN_BUDGET_CHANGE' ? 'bg-purple-100 text-purple-700' : '' }}">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="py-2 text-sm">{{ $log->details }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>
@endsection
