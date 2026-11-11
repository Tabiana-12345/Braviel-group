@extends('layouts.admin')

@section('title', $module['name'])

@section('content')
    <section class="page-heading">
        <div>
            <!-- <span class="form-kicker">MANAGEMENT</span> -->
            <h1>{{ $module['name'] }}</h1>
            <p>{{ $module['description'] }}</p>
        </div>
        <a class="admin-primary-button" href="{{ route('admin.modules.create', $moduleKey) }}">Add record <span aria-hidden="true">+</span></a>
    </section>

    <section class="data-card" aria-label="{{ $module['name'] }} records">
        @if ($records->isEmpty())
            <div class="table-empty-state">
                <span class="empty-chart-icon" aria-hidden="true">□</span>
                <h2>No records yet</h2>
                <p>Add your first {{ strtolower($module['name']) }} record. No sample data has been added.</p>
                <a class="admin-secondary-button" href="{{ route('admin.modules.create', $moduleKey) }}">Add first record</a>
            </div>
        @else
            <div class="table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            @foreach ($module['fields'] as $field)
                                <th>{{ $field['label'] }}</th>
                            @endforeach
                            @unless (in_array($moduleKey, ['brands', 'units'], true))
                                <th>Created</th>
                            @endunless
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td class="record-title">{{ $record->title }}</td>
                                @foreach ($module['fields'] as $field)
                                    <td>{{ \Illuminate\Support\Str::limit((string) ($record->data[$field['name']] ?? '—'), 70) }}</td>
                                @endforeach
                                @unless (in_array($moduleKey, ['brands', 'units'], true))
                                    <td>{{ $record->created_at->format('M j, Y') }}</td>
                                @endunless
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.modules.edit', [$moduleKey, $record->id]) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.modules.destroy', [$moduleKey, $record->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $records->links() }}</div>
        @endif
    </section>
@endsection
