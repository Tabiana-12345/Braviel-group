@extends('layouts.admin')

@section('title', 'View user')

@section('content')
    <section class="page-heading">
        <div>
            <span class="form-kicker">ACCESS CONTROL</span>
            <h1>User details</h1>
            <p>Account information and assigned role.</p>
        </div>
        <a class="admin-secondary-button" href="{{ route('admin.users.index') }}">Back to users</a>
    </section>

    <section class="data-card user-details-card">
        <dl>
            <div><dt>Username</dt><dd>{{ $managedUser->username }}</dd></div>
            <div><dt>Name</dt><dd>{{ $managedUser->name }}</dd></div>
            <div><dt>Role</dt><dd>{{ $managedUser->role_name }}</dd></div>
            <div><dt>Email</dt><dd>{{ $managedUser->email }}</dd></div>
        </dl>
        <a class="admin-primary-button" href="{{ route('admin.users.edit', $managedUser) }}">Edit user</a>
    </section>
@endsection
