<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7fb">
    <title>@yield('title', 'Admin') — Braviel Group</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/admin.js') }}" defer></script>
</head>
<body class="dashboard-page">
    @php
        $adminGroups = [
            [
                'name' => 'User Management',
                'icon' => 'users',
                'activePath' => 'admin/users',
                'activePaths' => ['admin/users', 'admin/roles'],
                'items' => [
                    ['name' => 'Users', 'route' => route('admin.users.index')],
                    ['name' => 'Roles', 'route' => route('admin.roles.index')],
                ],
            ],
            [
                'name' => 'Contacts',
                'icon' => 'address-book',
                'activePath' => 'admin/modules',
                'activePaths' => ['admin/modules/suppliers', 'admin/modules/customers'],
                'items' => [
                    ['name' => 'Suppliers', 'route' => route('admin.modules.index', 'suppliers')],
                    ['name' => 'Customers', 'route' => route('admin.modules.index', 'customers')],
                    ['name' => 'Import Contacts', 'route' => route('admin.modules.import.form', 'contacts')],
                ],
            ],
            [
                'name' => 'Products',
                'icon' => 'package',
                'activePath' => 'admin/modules',
                'activePaths' => ['admin/modules/products', 'admin/modules/units', 'admin/modules/categories', 'admin/modules/inventory', 'admin/modules/brands'],
                'items' => [
                    ['name' => 'List Product', 'route' => route('admin.modules.index', 'products')],
                    ['name' => 'Add Product', 'route' => route('admin.modules.create', 'products')],
                    ['name' => 'Import Product', 'route' => route('admin.modules.import.form', 'products')],
                    ['name' => 'Import Opening Stock', 'route' => route('admin.modules.import.form', 'inventory')],
                    ['name' => 'Update Price', 'route' => route('admin.modules.index', 'products')],
                    ['name' => 'Units', 'route' => route('admin.modules.index', 'units')],
                    ['name' => 'Categories', 'route' => route('admin.modules.index', 'categories')],
                    ['name' => 'Brands', 'route' => route('admin.modules.index', 'brands')],
                ],
            ],
            [
                'name' => 'Inventory',
                'icon' => 'warehouse',
                'activePath' => 'admin/modules/inventory',
                'items' => [
                    ['name' => 'List Inventory', 'route' => route('admin.modules.index', 'inventory')],
                    ['name' => 'Add Inventory', 'route' => route('admin.modules.create', 'inventory')],
                ],
            ],
            [
                'name' => 'Purchases',
                'icon' => 'shopping-bag',
                'activePath' => 'admin/modules/purchases',
                'activePaths' => ['admin/modules/purchases', 'admin/modules/purchase-returns'],
                'items' => [
                    ['name' => 'List Purchases', 'route' => route('admin.modules.index', 'purchases')],
                    ['name' => 'Add Purchase', 'route' => route('admin.modules.create', 'purchases')],
                    ['name' => 'List Purchase Return', 'route' => route('admin.modules.index', 'purchase-returns')],
                ],
            ],
            [
                'name' => 'Sell',
                'icon' => 'shopping-cart',
                'activePath' => 'admin/modules/sales',
                'items' => [
                    ['name' => 'All sales', 'route' => route('admin.modules.index', 'sales')],
                    ['name' => 'Add Sale', 'route' => route('admin.modules.create', 'sales')],
                    ['name' => 'List POS', 'route' => route('admin.modules.index', 'sales')],
                    ['name' => 'POS', 'route' => route('admin.modules.create', 'sales')],
                ],
            ],
        
            [
                'name' => 'Stock Adjustment',
                'icon' => 'sliders',
                'activePath' => 'admin/modules/stock-adjustments',
                'items' => [
                    ['name' => 'List Adjustments', 'route' => route('admin.modules.index', 'stock-adjustments')],
                    ['name' => 'Add Adjustment', 'route' => route('admin.modules.create', 'stock-adjustments')],
                ],
            ],
            [
                'name' => 'Expenses',
                'icon' => 'receipt',
                'activePath' => 'admin/modules/expenses',
                'items' => [
                    ['name' => 'List Expenses', 'route' => route('admin.modules.index', 'expenses')],
                    ['name' => 'Add Expense', 'route' => route('admin.modules.create', 'expenses')],
                ],
            ],
            [
                'name' => 'Payment',
                'icon' => 'credit-card',
                'activePath' => 'admin/modules/payments',
                'items' => [
                    ['name' => 'List Payments', 'route' => route('admin.modules.index', 'payments')],
                    ['name' => 'Add Payment', 'route' => route('admin.modules.create', 'payments')],
                ],
            ],
            [
                'name' => 'Returns and Refunds',
                'icon' => 'rotate-ccw',
                'activePath' => 'admin/modules/returns',
                'items' => [
                    ['name' => 'List Returns', 'route' => route('admin.modules.index', 'returns')],
                    ['name' => 'Add Return', 'route' => route('admin.modules.create', 'returns')],
                ],
            ],
            [
                'name' => 'Cash Register',
                'icon' => 'cash-register',
                'activePath' => 'admin/modules/cash-register',
                'items' => [
                    ['name' => 'List Registers', 'route' => route('admin.modules.index', 'cash-register')],
                    ['name' => 'Add Register', 'route' => route('admin.modules.create', 'cash-register')],
                ],
            ],
            [
                'name' => 'Reports and Analytics',
                'icon' => 'chart',
                'activePath' => 'admin/reports',
                'items' => [
                    ['name' => 'View Reports', 'route' => route('admin.reports.index')],
                ],
            ],
            [
                'name' => 'System Settings',
                'icon' => 'settings',
                'activePath' => 'admin/modules/settings',
                'items' => [
                    ['name' => 'Settings', 'route' => route('admin.modules.index', 'settings')],
                ],
            ],
            [
                'name' => 'Audit Logs and Backup',
                'icon' => 'clipboard',
                'activePath' => 'admin/audit-logs',
                'items' => [
                    ['name' => 'Audit Logs', 'route' => route('admin.audit.index')],
                ],
            ],
        ];
    @endphp
    <div class="admin-shell">
        <button class="sidebar-backdrop" type="button" aria-label="Close navigation menu" data-sidebar-close hidden></button>
        <aside class="admin-sidebar" id="admin-sidebar" aria-label="Main navigation">
            <a class="brand-lockup dashboard-brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">B<span>.</span></span>
                <span>braviel<span class="brand-light">group</span></span>
            </a>
            <nav class="admin-nav">
                <a class="admin-nav-link {{ request()->routeIs('dashboard') ? 'is-current' : '' }}" href="{{ route('dashboard') }}">
                    <span class="nav-icon" aria-hidden="true">⌂</span><span>Dashboard</span>
                </a>
                <div class="nav-section-label">MANAGEMENT</div>
                @foreach ($adminGroups as $group)
                    @php
                        $currentPath = request()->path();
                        $activePaths = $group['activePaths'] ?? [$group['activePath']];
                        $isGroupActive = collect($activePaths)->contains(fn ($path) => $currentPath === $path || str_starts_with($currentPath, $path.'/'));
                    @endphp
                    <details class="nav-group" @if ($isGroupActive) open @endif>
                        <summary class="admin-nav-link nav-group-toggle {{ $isGroupActive ? 'is-current' : '' }}">
                            <span class="nav-icon" aria-hidden="true">@include('components.admin-icon', ['name' => $group['icon']])</span>
                            <span>{{ $group['name'] }}</span>
                            <span class="nav-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="nav-submenu">
                            @foreach ($group['items'] as $item)
                                @php
                                    $itemPath = parse_url($item['route'], PHP_URL_PATH);
                                    $isItemActive = request()->getPathInfo() === $itemPath;
                                @endphp
                                <a class="admin-nav-sublink {{ $isItemActive ? 'is-current' : '' }}" href="{{ $item['route'] }}">{{ $item['name'] }}</a>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </nav>
            <div class="sidebar-account">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="sidebar-user-copy">
                    <span class="sidebar-user-name">{{ auth()->user()->name }}</span>
                    <span class="sidebar-user-email">{{ auth()->user()->email }}</span>
                </div>
            </div>
        </aside>
        <div class="admin-workspace">
            <header class="admin-topbar">
                <div class="topbar-leading">
                    <button class="sidebar-menu-toggle" type="button" aria-label="Open navigation menu" aria-controls="admin-sidebar" aria-expanded="false" data-sidebar-toggle>
                        <span class="menu-toggle-lines" aria-hidden="true"><span></span><span></span><span></span></span>
                    </button>
                    <div class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span>/</span><span>@yield('title', 'Admin')</span></div>
                </div>
                <div class="topbar-actions">
                    <details class="account-menu">
                        <summary class="account-menu-toggle" aria-label="Open account menu">
                            <span class="account-menu-avatar" aria-hidden="true">@include('components.admin-icon', ['name' => 'user-circle'])</span>
                            <span class="account-menu-name">{{ auth()->user()->name }}</span>
                            <span class="nav-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="account-menu-dropdown">
                            <div class="account-menu-heading">
                                <span class="sidebar-user-name">{{ auth()->user()->name }}</span>
                                <span class="sidebar-user-email">{{ auth()->user()->email }}</span>
                            </div>
                            <a class="account-menu-link" href="{{ route('profile.edit') }}">
                                <span class="account-menu-item-icon" aria-hidden="true">@include('components.admin-icon', ['name' => 'user-circle'])</span>
                                <span>My Profile</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="account-menu-link account-menu-signout" type="submit">
                                    <span class="account-menu-item-icon" aria-hidden="true">@include('components.admin-icon', ['name' => 'logout'])</span>
                                    <span>Sign out</span>
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
            </header>
            <main class="admin-main">
                @if (session('status'))
                    <div class="admin-notice admin-notice-success" role="status">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="admin-notice admin-notice-error" role="alert">
                        <p>Please correct the following:</p>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
                <footer class="dashboard-footer">© {{ date('Y') }} Braviel Group <span>·</span> Your workspace, your data.</footer>
            </main>
        </div>
    </div>
</body>
</html>
