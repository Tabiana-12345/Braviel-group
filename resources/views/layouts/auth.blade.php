
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Welcome') — Braviel Group</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/script.js') }}" defer></script>
</head>

<body class="auth-page">
    <main class="auth-shell">

        <aside class="brand-panel">

            <a class="brand-lockup" href="{{ route('login') }}" aria-label="Braviel Group home">
                <span class="brand-mark">B<span>.</span></span>
                <span>braviel<span class="brand-light">group</span></span>
            </a>

            <div class="brand-message">
                <span class="eyebrow">
                    <span class="eyebrow-dot"></span>
                    YOUR BUSINESS. ONE CONNECTED WORKSPACE.
                </span>

                <h1>
                    Think bigger.<br>
                    Work smarter.<br>
                    <em>Grow together.</em>
                </h1>

                <p>
                    Welcome to Braviel Group — your central workspace for
                    managing operations, connecting teams, and turning
                    everyday business activities into meaningful progress.
                </p>
            </div>

            <div class="brand-art" aria-hidden="true">
                <div class="art-orbit orbit-one"></div>
                <div class="art-orbit orbit-two"></div>
                <div class="art-orbit orbit-three"></div>
                <div class="art-core"><span>B</span></div>
                <span class="art-star star-one">✳</span>
                <span class="art-star star-two">✦</span>
                <span class="art-star star-three">✧</span>
            </div>

            <div class="brand-footer">
                <span>© {{ date('Y') }} Braviel Group. All rights reserved.</span>
                <span class="footer-divider"></span>
                <span>Excellence. Growth.</span>
            </div>

        </aside>

        <section class="form-panel">

            <div class="form-panel-top">
                <span class="mobile-brand">
                    <span class="brand-mark">B<span>.</span></span>
                    braviel<span>group</span>
                </span>

                <span class="secure-note">
                    <svg viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M4.5 7V5a3.5 3.5 0 0 1 7 0v2M3.5 7h9v7h-9z"/>
                    </svg>
                    Secure & Protected Access
                </span>
            </div>

            <div class="form-content">
                @yield('content')
            </div>

            <div class="form-panel-footer">
                <span>
                    Need assistance?
                    <a href="mailto:support@bravielgroup.com">We're here to help</a>
                </span>

                <span>
                    Your workspace, your privacy
                    <span class="footer-dot">·</span>
                    © {{ date('Y') }}
                </span>
            </div>

        </section>

    </main>
</body>
</html>
