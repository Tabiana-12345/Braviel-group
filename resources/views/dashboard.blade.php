@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $moduleItems = [];
        foreach (config('admin_modules') as $key => $module) {
            $moduleItems[] = [
                'id' => $key,
                'name' => $module['name'],
                'description' => $module['description'],
                'count' => $moduleCounts->get($key, 0),
                'route' => route('admin.modules.index', $key),
            ];
        }
        $moduleItems[] = [
            'id' => 'reports',
            'name' => 'Reports and Analytics',
            'description' => 'Review business performance',
            'count' => null,
            'route' => route('admin.reports.index'),
        ];
        $moduleItems[] = [
            'id' => 'users',
            'name' => 'User and Role Management',
            'description' => 'Manage staff access and roles',
            'count' => $usersCount,
            'route' => route('admin.users.index'),
        ];
        $moduleItems[] = [
            'id' => 'audit-backup',
            'name' => 'Audit Logs and Backup',
            'description' => 'Review activity and export workspace data',
            'count' => null,
            'route' => route('admin.audit.index'),
        ];
    @endphp

    <section class="dashboard-heading">
        <div>
            <!-- <span class="form-kicker">BUSINESS OVERVIEW</span> -->
            <h1>Dashboard</h1>
            <p>Welcome, {{ auth()->user()->name }}. Add your business information to get started.</p>
        </div>
        <!-- <span class="setup-badge"><span aria-hidden="true"></span> Workspace ready</span> -->
    </section>

    <section class="summary-grid" aria-label="Business summary">
        <article class="summary-card">
            <div class="summary-card-top"><span>Total Revenue</span><span class="summary-icon revenue-icon" aria-hidden="true">$</span></div>
            <strong>{{ $orderCount ? '$'.number_format($revenue, 2) : '—' }}</strong>
            <small>{{ $orderCount ? 'From recorded sales' : 'No sales data yet' }}</small>
        </article>
        <article class="summary-card">
            <div class="summary-card-top"><span>Total Orders</span><span class="summary-icon orders-icon" aria-hidden="true">↗</span></div>
            <strong>{{ $orderCount ?: '—' }}</strong>
            <small>{{ $orderCount ? 'Sales recorded' : 'No orders recorded' }}</small>
        </article>
        <article class="summary-card">
            <div class="summary-card-top"><span>Products</span><span class="summary-icon products-icon" aria-hidden="true">□</span></div>
            <strong>{{ $productCount ?: '—' }}</strong>
            <small>{{ $productCount ? 'Products added' : 'No products added' }}</small>
        </article>
        <article class="summary-card">
            <div class="summary-card-top"><span>Customers</span><span class="summary-icon customers-icon" aria-hidden="true">♙</span></div>
            <strong>{{ $customerCount ?: '—' }}</strong>
            <small>{{ $customerCount ? 'Customers added' : 'No customers added' }}</small>
        </article>
    </section>

    <section class="dashboard-sales-charts" aria-label="Sales charts">
        <x-dashboard-sales-chart
            title="Sales Last 30 Days"
            :labels="$salesLast30Days['labels']"
            :values="$salesLast30Days['values']"
            :label-interval="5"
        />
        <x-dashboard-sales-chart
            title="Sales Current Financial Year"
            :labels="$salesCurrentFinancialYear['labels']"
            :values="$salesCurrentFinancialYear['values']"
            :rotate-labels="false"
        />
    </section>

    <section class="overview-grid" aria-label="Business activity">
        <article class="overview-card sales-overview">
            <div class="overview-card-heading">
                <div><h2>Recent sales</h2><p>Sales recorded by your team.</p></div>
                <a class="text-action" href="{{ route('admin.modules.index', 'sales') }}">View sales</a>
            </div>
            @if ($recentSales->isEmpty())
                <div class="chart-empty-state">
                    <span class="empty-chart-icon" aria-hidden="true">⌁</span>
                    <span>No sales data yet</span>
                    <small>Record sales to see your business activity.</small>
                </div>
            @else
                <div class="recent-sales-list">
                    @foreach ($recentSales as $sale)
                        <div class="recent-sale-row">
                            <span>{{ $sale->title }}</span>
                            <strong>{{ isset($sale->data['total_amount']) ? number_format((float) $sale->data['total_amount'], 2) : '—' }}</strong>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>
        <article class="overview-card inventory-overview">
            <div class="overview-card-heading">
                <div><h2>Inventory overview</h2><p>Products and stock recorded.</p></div>
                <a class="text-action" href="{{ route('admin.modules.index', 'inventory') }}">View inventory</a>
            </div>
            @if ($productCount === 0)
                <div class="chart-empty-state">
                    <span class="empty-chart-icon" aria-hidden="true">□</span>
                    <span>No inventory data yet</span>
                    <small>Add products and inventory records to get started.</small>
                </div>
            @else
                <div class="inventory-summary">
                    <strong>{{ $productCount }}</strong>
                    <span>{{ \Illuminate\Support\Str::plural('product', $productCount) }} in your catalog</span>
                    <a class="text-action" href="{{ route('admin.modules.index', 'products') }}">Manage products</a>
                </div>
            @endif
        </article>
    </section>

   
@endsection
