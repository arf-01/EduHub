@extends('layout')

@section('title', 'Quiz Submitted — EduHub')

@section('custom_header')
    <x-nav role="student" />
@endsection

@section('content')
<div class="relative min-h-[calc(100vh-130px)] flex items-center justify-center px-4 py-16 overflow-hidden">

    {{-- Background blobs --}}
    <div class="edu-blob edu-animate-blob w-[500px] h-[500px] -top-40 -left-40 bg-indigo-700" style="opacity:.15;"></div>
    <div class="edu-blob edu-animate-blob w-[400px] h-[400px] -bottom-32 -right-32 bg-violet-600" style="opacity:.14; animation-delay:-4s;"></div>
    <div class="edu-blob edu-animate-blob w-[300px] h-[300px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-indigo-600" style="opacity:.06; animation-delay:-2s;"></div>

    <div class="relative z-10 w-full max-w-xl edu-animate-scale-in">
        <div class="edu-card p-8 sm:p-12 text-center shadow-2xl shadow-black/50">

            {{-- Trophy icon --}}
            <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-br from-amber-400/20 to-yellow-500/20 border border-amber-400/30 flex items-center justify-center text-4xl mb-6 edu-animate-float shadow-lg shadow-amber-500/15">
                🏆
            </div>

            <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-2">Great Work!</h1>
            <p class="text-base mb-8" style="color:var(--edu-text2);">
                You've successfully submitted your quiz.
            </p>

            {{-- Score display --}}
            <div class="inline-flex flex-col items-center px-8 py-5 rounded-2xl border mb-8"
                 style="background:var(--edu-card2); border-color:var(--edu-border2);">
                <span class="text-xs font-bold uppercase tracking-widest mb-1" style="color:var(--edu-text2);">Final Score</span>
                <span class="text-5xl font-black text-white" style="font-family:'JetBrains Mono',monospace;">
                    {{ number_format($score, 2) }}
                </span>
                <span class="text-sm mt-1" style="color:var(--edu-muted);">points</span>
            </div>

            {{-- Feature pills --}}
            <div class="flex flex-wrap justify-center gap-2 mb-10">
                <span class="edu-badge border" style="background:rgba(99,102,241,0.12); border-color:rgba(99,102,241,0.3); color:#a5b4fc; padding:.4rem .9rem;">
                    ⚡ Real-Time Results
                </span>
                <span class="edu-badge border" style="background:rgba(236,72,153,0.12); border-color:rgba(236,72,153,0.3); color:#f9a8d4; padding:.4rem .9rem;">
                    🏠 Room-Based Access
                </span>
                <span class="edu-badge border" style="background:rgba(6,182,212,0.12); border-color:rgba(6,182,212,0.3); color:#67e8f9; padding:.4rem .9rem;">
                    🛡️ Violation Detection
                </span>
            </div>

            {{-- Action buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/" class="edu-btn-ghost px-8 py-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Go Home
                </a>
            </div>

            <p class="mt-8 text-xs" style="color:var(--edu-muted);">
                Designed for seamless online evaluations. Trusted by educators. Built for the future.
            </p>
        </div>
    </div>
</div>
@endsection
