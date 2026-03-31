@extends('layouts.app')

@section('content')
<div class="card">
    <h2 class="text-xl font-bold mb-4">Edit User: {{ $user->name }}</h2>

    <form method="POST" action="{{ route('admin.user.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="border rounded w-full py-2 px-3" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="border rounded w-full py-2 px-3" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Role</label>
            <select name="role" class="border rounded w-full py-2 px-3">
                <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Budget Limit (₱)</label>
            <input type="number" name="budget_limit" value="{{ old('budget_limit', $user->budget_limit) }}" step="500" class="border rounded w-full py-2 px-3" required>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.user.view', $user) }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">Update User</button>
        </div>
    </form>
</div>
@endsection
