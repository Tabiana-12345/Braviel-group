@extends('layouts.admin')

@section('title', 'User and Role Management')

@section('content')
    <section class="page-heading">
        <div>
            <!-- <span class="form-kicker">ACCESS CONTROL</span> -->
            <h1>User and Role Management</h1>
            <p>Create accounts and assign administrator or staff access.</p>
        </div>
        <a class="admin-primary-button" href="{{ route('admin.users.create') }}">Add user <span aria-hidden="true">+</span></a>
    </section>

    <section class="data-card">
        @if ($users->isEmpty())
            <div class="table-empty-state"><h2>No users found</h2></div>
        @else
            <div class="table-scroll">
                <table class="admin-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="record-title">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="role-badge">{{ ucfirst($user->role) }}</span></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                        <a href="{{ route('admin.users.show', $user) }}">View</a>
                                        
                                        @if (! auth()->user()->is($user))
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit">Delete</button>
                                            </form>
                                        @else
                                            <button type="button" disabled title="You cannot delete your own account">Delete</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
