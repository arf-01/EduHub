@extends('layout')

@section('title', 'Set New Password — EduHub')

@section('custom_header')
    <x-nav />
@endsection

@section('suppress_layout_flash', true)

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
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-white mb-1">Set New Password</h1>
                <p class="text-sm" style="color:var(--edu-text2);">Choose a secure password for your account</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 px-4 py-3 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-300 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-start gap-1.5">
                            <span class="mt-0.5">•</span> {{ $error }}
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Email Address
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                           placeholder="you@example.com"
                           class="edu-input">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        New Password
                    </label>
                    <input id="password" type="password" name="password" required
                           placeholder="Min. 8 characters"
                           class="edu-input">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Confirm Password
                    </label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           placeholder="Re-enter password"
                           class="edu-input">
                </div>

                <button type="submit" id="reset-password-btn" class="edu-btn-primary w-full text-base py-3 mt-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Password
                </button>
            </form>

            <p class="mt-8 text-center text-sm" style="color:var(--edu-text2);">
                Back to
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">
                    Sign in
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
