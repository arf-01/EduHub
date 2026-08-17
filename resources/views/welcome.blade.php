@extends('layout')

@section('title', 'EduHub — Quiz Platform for Teachers & Students')
@section('meta_description', 'EduHub is a modern quiz platform for teachers and students. Create quizzes, track progress, and empower learning.')

@section('custom_header')
    <x-nav />
@endsection

@section('content')
<div class="relative min-h-[calc(100vh-130px)] flex items-center justify-center overflow-hidden px-4 py-20">

    {{-- Background blobs --}}
    <div class="edu-blob edu-animate-blob w-[600px] h-[600px] -top-60 -left-60 bg-indigo-700" style="opacity:.18;"></div>
    <div class="edu-blob edu-animate-blob w-[500px] h-[500px] -bottom-40 -right-40 bg-violet-600" style="opacity:.15; animation-delay:-4s;"></div>
    <div class="edu-blob edu-animate-blob w-[300px] h-[300px] top-1/3 left-2/3 bg-cyan-700" style="opacity:.08; animation-delay:-2s;"></div>

    {{-- Grid overlay --}}
    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(99,102,241,0.04) 1px, transparent 1px), linear-gradient(to right, rgba(99,102,241,0.04) 1px, transparent 1px); background-size: 50px 50px;"></div>

    {{-- Hero content --}}
    <div class="relative z-10 max-w-3xl w-full text-center edu-animate-scale-in">

        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border mb-6 text-xs font-semibold"
             style="background:rgba(99,102,241,0.1); border-color:rgba(99,102,241,0.3); color:#a5b4fc;">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 inline-block edu-animate-live"></span>
            Online Quiz Platform · Real-Time Results
        </div>

        <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-white leading-tight mb-5">
            Unlock Your
            <span style="background: linear-gradient(135deg, #a5b4fc 0%, #6366f1 40%, #c4b5fd 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                Learning Potential
            </span>
        </h1>

        <p class="text-base sm:text-lg max-w-2xl mx-auto mb-10" style="color:var(--edu-text2);">
            Join EduHub — a modern quiz platform with real-time results, room-based access, and advanced analytics. Whether you're a teacher or student, we've got you covered.
        </p>

        {{-- CTA buttons --}}
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <form action="{{ route('TorS') }}" method="GET">
                <button name="role" value="student" id="student-cta"
                        class="edu-btn-primary w-full sm:w-auto px-8 py-3.5 text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    I'm a Student
                </button>
            </form>

            <form action="{{ route('TorS') }}" method="GET">
                <button name="role" value="teacher" id="teacher-cta"
                        class="edu-btn-ghost w-full sm:w-auto px-8 py-3.5 text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    I'm a Teacher
                </button>
            </form>
        </div>

        {{-- Feature chips --}}
        <div class="flex flex-wrap justify-center gap-3 mt-10">
            @foreach(['⚡ Real-Time Results', '🏠 Room-Based Access', '📊 Analytics', '🛡️ Violation Detection', '📱 Works Offline'] as $f)
                <span class="edu-badge border py-1.5 px-3"
                      style="background:rgba(255,255,255,0.04); border-color:var(--edu-border2); color:var(--edu-text2); font-size:.75rem;">
                    {{ $f }}
                </span>
            @endforeach
        </div>

    </div>
</div>
@endsection
