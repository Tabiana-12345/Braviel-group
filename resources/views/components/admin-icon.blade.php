<svg class="admin-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="10" cy="7" r="4"/>
            <path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            @break
        @case('address-book')
            <rect x="5" y="3" width="16" height="18" rx="2"/>
            <path d="M9 3v18M3 8h2M3 13h2M3 18h2"/>
            <circle cx="14" cy="10" r="2"/>
            <path d="M11 17a3 3 0 0 1 6 0"/>
            @break
        @case('package')
            <path d="m12 3 9 5-9 5-9-5 9-5Z"/>
            <path d="M3 8v9l9 5 9-5V8M12 13v9M7.5 5.5l9 5"/>
            @break
        @case('warehouse')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10Z"/>
            <path d="M7 21v-7h10v7M7 10h.01M12 10h.01M17 10h.01"/>
            @break
        @case('shopping-bag')
            <path d="M5 8h14l1 13H4L5 8Z"/>
            <path d="M9 9V6a3 3 0 0 1 6 0v3"/>
            @break
        @case('shopping-cart')
            <circle cx="9" cy="20" r="1"/>
            <circle cx="19" cy="20" r="1"/>
            <path d="M2 3h2l2.4 12.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6L22 8H5"/>
            @break
        @case('truck')
            <path d="M3 6h11v12H3zM14 10h4l3 3v5h-7"/>
            <circle cx="7.5" cy="19" r="1.5"/>
            <circle cx="17.5" cy="19" r="1.5"/>
            @break
        @case('sliders')
            <path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"/>
            <path d="M2 14h4M10 8h4M18 16h4"/>
            @break
        @case('receipt')
            <path d="M4 3h16v18l-3-2-3 2-2-2-3 2-3-2-2 2V3Z"/>
            <path d="M8 8h8M8 12h8M8 16h4"/>
            @break
        @case('credit-card')
            <rect x="2" y="5" width="20" height="14" rx="2"/>
            <path d="M2 10h20M6 15h3"/>
            @break
        @case('rotate-ccw')
            <path d="M3 11a9 9 0 1 1 2.6 6.4L3 15"/>
            <path d="M3 4v7h7"/>
            @break
        @case('cash-register')
            <path d="M4 10h16v11H4zM7 10V5h10v5M7 14h.01M11 14h.01M15 14h.01M7 18h10"/>
            <path d="M9 5V3h6v2"/>
            @break
        @case('chart')
            <path d="M3 3v18h18"/>
            <path d="m7 14 4-4 4 3 6-7"/>
            @break
        @case('settings')
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.7 2.94-.08-.03a1.7 1.7 0 0 0-1.86.35 1.7 1.7 0 0 0-.69 1.56V22h-3.4v-.1a1.7 1.7 0 0 0-1.16-1.58 1.7 1.7 0 0 0-1.88.34l-.06.06-2.94-1.7.03-.08a1.7 1.7 0 0 0-.35-1.86 1.7 1.7 0 0 0-1.56-.69H4v-3.4h.1a1.7 1.7 0 0 0 1.58-1.16 1.7 1.7 0 0 0-.34-1.88l-.06-.06 1.7-2.94.08.03a1.7 1.7 0 0 0 1.86-.35 1.7 1.7 0 0 0 .69-1.56V5h3.4v.1a1.7 1.7 0 0 0 1.16 1.58 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.94 1.7-.03.08a1.7 1.7 0 0 0 .35 1.86 1.7 1.7 0 0 0 1.56.69h.1v3.4h-.1a1.7 1.7 0 0 0-1.58 1.16Z"/>
            @break
        @case('clipboard')
            <rect x="5" y="4" width="14" height="17" rx="2"/>
            <path d="M9 4.5h6a1.5 1.5 0 0 0-3-1 1.5 1.5 0 0 0-3 1ZM9 11h6M9 15h6"/>
            @break
        @case('user-circle')
            <circle cx="12" cy="12" r="10"/>
            <circle cx="12" cy="9" r="3"/>
            <path d="M6.5 19a6 6 0 0 1 11 0"/>
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5M15 12H3"/>
            <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>
            @break
        @case('download')
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <path d="m7 10 5 5 5-5M12 15V3"/>
            @break
        @case('printer')
            <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <path d="M6 14h12v8H6zM18 12h.01"/>
            @break
        @case('file')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <path d="M14 2v6h6M8 13h8M8 17h8"/>
            @break
        @case('eye')
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
            <circle cx="12" cy="12" r="3"/>
            @break
        @case('edit')
            <path d="m16 4 4 4M4 20l4-.8L19 8a2.1 2.1 0 0 0-3-3L5 16l-1 4Z"/>
            @break
        @case('trash')
            <path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/>
            @break
    @endswitch
</svg>
