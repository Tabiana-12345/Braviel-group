@extends('layouts.admin')

@section('title', 'Roles')

@section('content')
    <section class="page-heading">
        <div>
            <!-- <span class="form-kicker">ACCESS CONTROL</span> -->
            <h1>Roles</h1>
            <p>Create roles and choose which menu actions each role can use.</p>
        </div>
        <a class="admin-primary-button" href="{{ route('admin.roles.create') }}">Add role <span aria-hidden="true">+</span></a>
    </section>

    <section class="data-card">
        @if ($roles->isEmpty())
            <div class="table-empty-state">
                <h2>No roles yet</h2>
                <p>Create a role and configure its menu permissions.</p>
            </div>
        @else
            <div class="table-scroll">
                <table class="admin-table">
                    <thead><tr><th>Role</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td class="record-title">{{ $role->name }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.roles.edit', $role) }}">Edit </a>
                                        @unless ($role->is_system)
                                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit">Delete</button>
                                            </form>
                                        @else
                                            <button type="button" disabled title="Built-in roles cannot be deleted">Delete</button>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
