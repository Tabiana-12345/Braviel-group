<?php

return [
    'products' => [
        'name' => 'Product Management',
        'description' => 'Manage your product catalog',
        'fields' => [
            ['name' => 'sku', 'label' => 'SKU', 'type' => 'text'],
            ['name' => 'brand', 'label' => 'Brand', 'type' => 'select', 'source' => 'brands'],
            ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'source' => 'categories'],
            ['name' => 'unit', 'label' => 'Unit', 'type' => 'select', 'source' => 'units'],
            ['name' => 'purchase_price', 'label' => 'Purchase price', 'type' => 'number'],
            ['name' => 'sale_price', 'label' => 'Sale price', 'type' => 'number'],
            ['name' => 'stock_quantity', 'label' => 'Stock quantity', 'type' => 'number'],
        ],
    ],
    'categories' => [
        'name' => 'Category Management',
        'description' => 'Organize products into categories',
        'fields' => [
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
        ],
    ],
    'units' => [
        'name' => 'Units',
        'description' => 'Manage units used to measure products',
        'fields' => [
            ['name' => 'abbreviation', 'label' => 'Short name', 'type' => 'text'],
        ],
    ],
    'brands' => [
        'name' => 'Brands',
        'description' => 'Manage product brands',
        'fields' => [
            ['name' => 'short_name', 'label' => 'Short name', 'type' => 'text'],
        ],
    ],
    'sales' => [
        'name' => 'Sales and Point of Sale (POS)',
        'description' => 'Record and manage sales',
        'fields' => [
            ['name' => 'customer', 'label' => 'Customer', 'type' => 'text'],
            ['name' => 'sale_date', 'label' => 'Sale date', 'type' => 'date'],
            ['name' => 'total_amount', 'label' => 'Total amount', 'type' => 'number'],
            ['name' => 'payment_method', 'label' => 'Payment method', 'type' => 'text'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'text'],
        ],
    ],
    'inventory' => [
        'name' => 'Inventory Management',
        'description' => 'Track stock and adjustments',
        'fields' => [
            ['name' => 'product', 'label' => 'Product', 'type' => 'text'],
            ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number'],
            ['name' => 'movement_type', 'label' => 'Movement type', 'type' => 'text'],
            ['name' => 'movement_date', 'label' => 'Movement date', 'type' => 'date'],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
        ],
    ],
    'stock-transfers' => [
        'name' => 'Stock Transfers',
        'description' => 'Record stock moved between locations',
        'fields' => [
            ['name' => 'product', 'label' => 'Product', 'type' => 'text'],
            ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number'],
            ['name' => 'source_location', 'label' => 'Source location', 'type' => 'text'],
            ['name' => 'destination_location', 'label' => 'Destination location', 'type' => 'text'],
            ['name' => 'transfer_date', 'label' => 'Transfer date', 'type' => 'date'],
        ],
    ],
    'stock-adjustments' => [
        'name' => 'Stock Adjustments',
        'description' => 'Record stock corrections',
        'fields' => [
            ['name' => 'product', 'label' => 'Product', 'type' => 'text'],
            ['name' => 'quantity_change', 'label' => 'Quantity change', 'type' => 'number'],
            ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea'],
            ['name' => 'adjustment_date', 'label' => 'Adjustment date', 'type' => 'date'],
        ],
    ],
    'purchases' => [
        'name' => 'Purchases',
        'description' => 'Manage purchase orders',
        'fields' => [
            ['name' => 'supplier', 'label' => 'Supplier', 'type' => 'text'],
            ['name' => 'purchase_date', 'label' => 'Purchase date', 'type' => 'date'],
            ['name' => 'total_amount', 'label' => 'Total amount', 'type' => 'number'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'text'],
        ],
    ],
    'purchase-returns' => [
        'name' => 'Purchase Returns',
        'description' => 'Manage returned purchases',
        'fields' => [
            ['name' => 'supplier', 'label' => 'Supplier', 'type' => 'text'],
            ['name' => 'return_date', 'label' => 'Return date', 'type' => 'date'],
            ['name' => 'total_amount', 'label' => 'Total amount', 'type' => 'number'],
            ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea'],
        ],
    ],
    'customers' => [
        'name' => 'Customer',
        'description' => 'Manage customer records',
        'fields' => [
            ['name' => 'contact_name', 'label' => 'Contact name', 'type' => 'text'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
            ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
        ],
    ],
    'suppliers' => [
        'name' => 'Supplier',
        'description' => 'Manage supplier records',
        'fields' => [
            ['name' => 'contact_name', 'label' => 'Contact name', 'type' => 'text'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
            ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
        ],
    ],
    'expenses' => [
        'name' => 'Expense Management',
        'description' => 'Record business expenses',
        'fields' => [
            ['name' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number'],
            ['name' => 'expense_date', 'label' => 'Expense date', 'type' => 'date'],
            ['name' => 'payment_method', 'label' => 'Payment method', 'type' => 'text'],
        ],
    ],
    'payments' => [
        'name' => 'Payment Management',
        'description' => 'Track incoming and outgoing payments',
        'fields' => [
            ['name' => 'reference', 'label' => 'Reference', 'type' => 'text'],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number'],
            ['name' => 'payment_date', 'label' => 'Payment date', 'type' => 'date'],
            ['name' => 'payment_method', 'label' => 'Payment method', 'type' => 'text'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'text'],
        ],
    ],
    'returns' => [
        'name' => 'Returns and Refunds',
        'description' => 'Manage returned items and refunds',
        'fields' => [
            ['name' => 'sale_reference', 'label' => 'Sale reference', 'type' => 'text'],
            ['name' => 'return_date', 'label' => 'Return date', 'type' => 'date'],
            ['name' => 'refund_amount', 'label' => 'Refund amount', 'type' => 'number'],
            ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea'],
        ],
    ],
    'cash-register' => [
        'name' => 'Cash Register',
        'description' => 'Manage register sessions',
        'fields' => [
            ['name' => 'opening_balance', 'label' => 'Opening balance', 'type' => 'number'],
            ['name' => 'closing_balance', 'label' => 'Closing balance', 'type' => 'number'],
            ['name' => 'opened_at', 'label' => 'Opened at', 'type' => 'datetime-local'],
            ['name' => 'closed_at', 'label' => 'Closed at', 'type' => 'datetime-local'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'text'],
        ],
    ],
    'settings' => [
        'name' => 'System Settings',
        'description' => 'Configure your workspace',
        'fields' => [
            ['name' => 'setting_key', 'label' => 'Setting key', 'type' => 'text'],
            ['name' => 'setting_value', 'label' => 'Setting value', 'type' => 'textarea'],
        ],
    ],
];
