@extends('layout')

@section('title', 'Join a Room — EduHub')
@section('meta_description', 'Enter your Student ID and room name to access your quiz.')

@section('custom_header')
    <x-nav role="student" />
@endsection

@section('suppress_layout_flash', true)

@section('content')
<div class="relative min-h-[calc(100vh-130px)] flex items-center justify-center px-4 py-16 overflow-hidden">

    {{-- Animated background blobs --}}
    <div class="edu-blob edu-animate-blob w-[500px] h-[500px] -top-40 -left-40 bg-indigo-700"></div>
    <div class="edu-blob edu-animate-blob w-[400px] h-[400px] -bottom-32 -right-32 bg-violet-600" style="animation-delay:-3s;"></div>

    <div class="relative z-10 w-full max-w-md edu-animate-scale-in">

        {{-- Card --}}
        <div class="edu-card p-8 sm:p-10 shadow-2xl shadow-black/40">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 mb-4 edu-animate-float">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-white mb-1">Join a Quiz Room</h1>
                <p class="text-sm" style="color:var(--edu-text2);">Enter your details to access your assigned quiz</p>
            </div>

            {{-- Error alert --}}
            @if (session('error'))
                <div class="mb-6 px-4 py-3 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-300 text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('enter.room') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="student_id" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Student ID
                    </label>
                    <input
                        type="text"
                        id="student_id"
                        name="student_id"
                        required
                        placeholder="e.g. STU-2024-001"
                        class="edu-input"
                        value="{{ old('student_id') }}"
                    >
                </div>

                <div>
                    <label for="room_name" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Room Name
                    </label>
                    <input
                        type="text"
                        id="room_name"
                        name="room_name"
                        required
                        placeholder="e.g. CS101-FINAL"
                        class="edu-input"
                        value="{{ old('room_name') }}"
                    >
                </div>

                <button type="submit" id="enter-room-btn" class="edu-btn-primary w-full mt-2 text-base py-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Enter Room
                </button>
            </form>

            <p class="mt-6 text-center text-xs" style="color:var(--edu-muted);">
                Make sure your room name is correct. Contact your teacher if you can't find your room.
            </p>
        </div>

    </div>
</div>
@endsection
