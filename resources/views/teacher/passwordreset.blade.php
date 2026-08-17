@extends('layout')

@section('title', 'Reset Password — EduHub')

@section('custom_header')
    <x-nav />
@endsection

@section('content')
<div class="relative min-h-[calc(100vh-130px)] flex items-center justify-center px-4 py-16 overflow-hidden">

    <div class="edu-blob edu-animate-blob w-[500px] h-[500px] -top-40 -left-40 bg-indigo-700"></div>
    <div class="edu-blob edu-animate-blob w-[400px] h-[400px] -bottom-32 -right-32 bg-violet-600" style="animation-delay:-3s;"></div>

    <div class="relative z-10 w-full max-w-md edu-animate-scale-in">
        <div class="edu-card p-8 sm:p-10 shadow-2xl shadow-black/50">

            <div class="text-center mb-8">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 mb-4">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-white mb-1">Reset Password</h1>
                <p class="text-sm" style="color:var(--edu-text2);">Enter your email and we'll send a reset link</p>
            </div>

            @if (session('status'))
                <div class="mb-5 px-4 py-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-300 text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" novalidate class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Email Address
                    </label>
                    <input type="email" id="email" name="email"
                           placeholder="you@example.com"
                           value="{{ old('email') }}"
                           required autofocus
                           class="edu-input">
                    @error('email')
                        <p class="text-xs mt-1.5 text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" id="send-reset-btn" class="edu-btn-primary w-full text-base py-3 mt-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Send Reset Link
                </button>
            </form>

            <p class="mt-6 text-center text-sm" style="color:var(--edu-text2);">
                Remember your password?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection
