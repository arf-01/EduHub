@extends('layout')

@section('title', 'Teacher Registration — EduHub')
@section('meta_description', 'Create your EduHub teacher account to start creating quizzes.')

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
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-violet-500/30 mb-4">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-white mb-1">Create Account</h1>
                <p class="text-sm" style="color:var(--edu-text2);">Set up your teacher account in seconds</p>
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

            <form action="{{ route('registration.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Full Name
                    </label>
                    <input type="text" id="name" name="name" required
                           placeholder="e.g. Dr. Jane Smith"
                           value="{{ old('name') }}"
                           class="edu-input">
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required
                           placeholder="you@example.com"
                           value="{{ old('email') }}"
                           class="edu-input">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Password
                    </label>
                    <input type="password" id="password" name="password" required
                           placeholder="Min. 8 characters"
                           class="edu-input">
                </div>

                <div>
                    <label for="room_name" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Room Name
                    </label>
                    <input type="text" id="room_name" name="room_name" required
                           placeholder="e.g. CS101-SPRING"
                           value="{{ old('room_name') }}"
                           class="edu-input">
                    <p class="text-xs mt-1.5" style="color:var(--edu-muted);">
                        Students will use this name to join your quizzes
                    </p>
                </div>

                <button type="submit" id="register-btn" class="edu-btn-primary w-full text-base py-3 mt-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Create Account
                </button>
            </form>

            <p class="mt-8 text-center text-sm" style="color:var(--edu-text2);">
                Already have an account?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">
                    Sign in
                </a>
            </p>

        </div>
    </div>
</div>
@endsection
