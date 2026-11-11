@extends('layouts.admin')

@section('title', 'Reports and Analytics')

@section('content')
    <section class="page-heading">
        <div><span class="form-kicker">BUSINESS OVERVIEW</span><h1>Reports and Analytics</h1><p>Figures are calculated only from records entered by your team.</p></div>
    </section>

    <section class="summary-grid report-summary">
        <article class="summary-card"><div class="summary-card-top"><span>Recorded Sales</span></div><strong>{{ $counts->get('sales', 0) }}</strong><small>Sales records</small></article>
        <article class="summary-card"><div class="summary-card-top"><span>Revenue</span></div><strong>{{ $counts->get('sales', 0) ? number_format($revenue, 2) : '—' }}</strong><small>{{ $counts->get('sales', 0) ? 'From entered sales' : 'No sales data yet' }}</small></article>
        <article class="summary-card"><div class="summary-card-top"><span>Expenses</span></div><strong>{{ $counts->get('expenses', 0) ? number_format($expenses, 2) : '—' }}</strong><small>{{ $counts->get('expenses', 0) ? 'From entered expenses' : 'No expense data yet' }}</small></article>
        <article class="summary-card"><div class="summary-card-top"><span>Products</span></div><strong>{{ $counts->get('products', 0) }}</strong><small>Product records</small></article>
    </section>

    <section class="data-card report-records">
        <div class="data-card-heading"><div><h2>Latest records</h2><p>Recent data entered across your modules.</p></div></div>
        @if ($recentRecords->isEmpty())
            <div class="table-empty-state">
                <span class="empty-chart-icon" aria-hidden="true">⌁</span>
                <h2>No report data yet</h2>
                <p>As your team adds records, summaries will appear here.</p>
            </div>
        @else
            <div class="table-scroll">
                <table class="admin-table">
                    <thead><tr><th>Record</th><th>Module</th><th>Added</th></tr></thead>
                    <tbody>
                        @foreach ($recentRecords as $record)
                            <tr><td class="record-title">{{ $record->title }}</td><td>{{ $modules[$record->module]['name'] ?? $record->module }}</td><td>{{ $record->created_at->format('M j, Y') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
