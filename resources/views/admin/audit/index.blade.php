@extends('layouts.admin')

@section('title', 'Audit Logs and Backup')

@section('content')
    <section class="page-heading">
        <div><span class="form-kicker">SYSTEM ACTIVITY</span><h1>Audit Logs and Backup</h1><p>Audit entries are generated automatically and cannot be edited.</p></div>
        <form method="POST" action="{{ route('admin.audit.backup') }}">
            @csrf
            <button class="admin-primary-button" type="submit">Export workspace backup <span aria-hidden="true">↓</span></button>
        </form>
    </section>

    <section class="data-card">
        <div class="data-card-heading"><div><h2>Audit log</h2><p>Changes made by administrators and workspace exports.</p></div></div>
        @if ($logs->isEmpty())
            <div class="table-empty-state">
                <span class="empty-chart-icon" aria-hidden="true">◷</span>
                <h2>No activity recorded</h2>
                <p>Administrative changes will be listed here as they happen.</p>
            </div>
        @else
            <div class="table-scroll">
                <table class="admin-table">
                    <thead><tr><th>Activity</th><th>Module</th><th>Action</th><th>Administrator</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td class="record-title">{{ $log->description }}</td>
                                <td>{{ str($log->module)->replace('-', ' ')->title() }}</td>
                                <td>{{ str($log->action)->replace('-', ' ')->title() }}</td>
                                <td>{{ $log->user?->name ?? 'Account removed' }}</td>
                                <td>{{ $log->created_at->format('M j, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $logs->links() }}</div>
        @endif
    </section>
    <p class="backup-note">The export is generated when requested and contains workspace records and user names/emails/roles. Passwords are never included.</p>
@endsection
