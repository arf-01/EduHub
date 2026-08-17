<!doctype html>
<html lang="en" class="h-full" style="background:#080b14;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'EduHub — Quiz System')</title>
    <meta name="description" content="@yield('meta_description', 'EduHub — A modern quiz platform for teachers and students.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @stack('head')
</head>
<body class="min-h-screen flex flex-col antialiased @yield('body_class', '')" style="font-family: 'Inter', system-ui, sans-serif; background: var(--edu-bg); color: var(--edu-text);">

    {{-- Navigation: pages can override with custom_header or the layout renders the default nav --}}
    @hasSection('custom_header')
        @yield('custom_header')
    @else
        <x-nav />
    @endif

    {{-- Flash / Toast messages --}}
    @if(session('success'))
        <div id="flash-success" class="fixed top-20 right-5 z-[999] max-w-sm w-full px-5 py-3.5 rounded-2xl border border-emerald-500/40 bg-emerald-950/80 backdrop-blur-xl text-emerald-300 text-sm font-semibold shadow-2xl flex items-center gap-3 edu-animate-slide-up" style="animation-delay:.05s;">
            <span class="text-emerald-400 text-base">✓</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error') && !View::hasSection('suppress_layout_flash'))
        <div id="flash-error" class="fixed top-20 right-5 z-[999] max-w-sm w-full px-5 py-3.5 rounded-2xl border border-rose-500/40 bg-rose-950/80 backdrop-blur-xl text-rose-300 text-sm font-semibold shadow-2xl flex items-center gap-3 edu-animate-slide-up" style="animation-delay:.05s;">
            <span class="text-rose-400 text-base">✕</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- Main Content --}}
    @hasSection('full_bleed')
        <main class="flex-grow">
            @yield('content')
        </main>
    @else
        <main class="flex-grow">
            @yield('content')
        </main>
    @endif

    {{-- Footer --}}
    <footer class="border-t py-8 mt-auto" style="border-color: var(--edu-border); background: var(--edu-surface);">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-md bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="text-sm font-bold" style="color:var(--edu-text2);">EduHub</span>
            </div>
            <p class="text-xs" style="color:var(--edu-muted);">
                &copy; {{ date('Y') }} EduHub. Empowering learning, one quiz at a time.
            </p>
            <div class="flex items-center gap-4 text-xs" style="color:var(--edu-muted);">
                <a href="/" class="hover:text-indigo-400 transition">Home</a>
                <span>&middot;</span>
                <span>Built with ❤️</span>
            </div>
        </div>
    </footer>

    @stack('scripts')

    {{-- Auto-dismiss flash toasts --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            ['flash-success', 'flash-error'].forEach(id => {
                const el = document.getElementById(id);
                if (el) setTimeout(() => {
                    el.style.transition = 'opacity 0.4s, transform 0.4s';
                    el.style.opacity = '0';
                    el.style.transform = 'translateX(20px)';
                    setTimeout(() => el.remove(), 400);
                }, 3500);
            });
        });
    </script>

    {{-- Service Worker --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>
</html>
