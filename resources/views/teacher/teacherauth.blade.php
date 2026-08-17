@extends('layout')

@section('title', 'Teacher Login — EduHub')
@section('meta_description', 'Sign in to your EduHub teacher account to manage quizzes.')

@section('custom_header')
    <x-nav />
@endsection

@section('suppress_layout_flash', true)

@section('content')
<div class="relative min-h-[calc(100vh-130px)] flex items-center justify-center px-4 py-16 overflow-hidden">

    {{-- Background blobs --}}
    <div class="edu-blob edu-animate-blob w-[500px] h-[500px] -top-40 -left-40 bg-indigo-700"></div>
    <div class="edu-blob edu-animate-blob w-[400px] h-[400px] -bottom-32 -right-32 bg-violet-600" style="animation-delay:-3s;"></div>

    <div class="relative z-10 w-full max-w-md edu-animate-scale-in">
        <div class="edu-card p-8 sm:p-10 shadow-2xl shadow-black/50">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 mb-4">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-white mb-1">Teacher Login</h1>
                <p class="text-sm" style="color:var(--edu-text2);">Sign in to manage your quizzes and students</p>
            </div>

            {{-- Alerts --}}
            @if (session('error'))
                <div class="mb-5 px-4 py-3 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-300 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-5 px-4 py-3 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-300 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-start gap-1.5">
                            <span class="mt-0.5">•</span> {{ $error }}
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required
                           value="{{ old('email') }}"
                           placeholder="you@example.com"
                           class="edu-input">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="text-xs font-semibold uppercase tracking-wider" style="color:var(--edu-text2);">
                            Password
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-indigo-400 hover:text-indigo-300 transition">
                            Forgot password?
                        </a>
                    </div>
                    <input type="password" id="password" name="password" required
                           placeholder="••••••••"
                           class="edu-input">
                </div>

                <button type="submit" id="login-btn" class="edu-btn-primary w-full text-base py-3 mt-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Sign In
                </button>
            </form>

            <p class="mt-8 text-center text-sm" style="color:var(--edu-text2);">
                Don't have an account?
                <a href="{{ route('registration') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">
                    Register here
                </a>
            </p>

        </div>
    </div>
</div>
@endsection
