<!doctype html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Quiz System')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Header -->
    @hasSection('custom_header')
        @yield('custom_header')
    @endif
    <!-- If you want a default header for other pages without custom_header, add it here: -->
    {{-- 
    @unless (View::hasSection('custom_header'))
        <header class="bg-violet-600 text-white shadow z-50">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold">📘 Quiz System</h1>
                <nav class="space-x-4">
                    <a href="/" class="hover:underline">Home</a>
                    <a href="#" class="hover:underline">Blogs</a>
                    <!-- Add logout or other links here if needed -->
                </nav>
            </div>
        </header>
    @endunless
    --}}

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-800 text-white text-center py-4 mt-auto">
        &copy; {{ date('Y') }} Quiz System. All rights reserved.
    </footer>

    @stack('scripts')
</body>
</html>
