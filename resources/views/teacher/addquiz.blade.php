@extends('layout')

@section('title', 'Create a New Quiz — EduHub')
@section('meta_description', 'Create a new quiz, add questions, set timers, and publish.')

@section('custom_header')
    <x-nav role="teacher" />
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
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 mb-4 edu-animate-float">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-white mb-1">Create a New Quiz</h1>
                <p class="text-sm" style="color:var(--edu-text2);">Give your quiz a title to start adding questions</p>
            </div>

            @if (session('success'))
                <div class="mb-5 px-4 py-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-300 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('storeQuiz') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="quiz_title" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                        Quiz Title
                    </label>
                    <input 
                        type="text" 
                        id="quiz_title" 
                        name="quiz_title" 
                        placeholder="e.g. CS101 Midterm Examination" 
                        required
                        class="edu-input text-base"
                    >
                </div>

                <button 
                    type="submit" 
                    id="submit-quiz-btn"
                    class="edu-btn-primary w-full text-base py-3"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Continue to Question Studio
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-800 text-center">
                <a href="{{ route('quiz.list') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition inline-flex items-center gap-1">
                    ← Back to All Quizzes
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
