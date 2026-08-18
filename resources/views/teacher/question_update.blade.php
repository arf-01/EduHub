@extends('layout')

@section('title', 'Edit Question #' . $question->id . ' — ' . $quiz->title . ' — EduHub')
@section('full_bleed', true)

@section('custom_header')
    <x-nav role="teacher" :quiz-title="$quiz->title" />
@endsection

@section('content')
<div style="background:var(--edu-bg); min-height:calc(100vh - 130px);">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto py-6 sm:py-10 px-4 sm:px-6 space-y-6 text-slate-100">

    <!-- Top Breadcrumb Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
            <a href="{{ route('quiz.details', $quiz->id) }}" class="hover:text-indigo-400 transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to {{ $quiz->title }}
            </a>
            <span>/</span>
            <span class="text-slate-200">Edit Question #{{ $question->id }}</span>
        </div>

        <a href="{{ route('quiz.details', $quiz->id) }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1">
            <span>✕ Close Editor</span>
        </a>
    </div>

    <!-- Unified Question Studio Component in Edit Mode -->
    <x-question-studio.editor :quiz="$quiz" :question="$question" :isEdit="true" />

</div>

<!-- Floating Toast Container -->
<div id="toast-container" class="fixed bottom-5 right-5 z-50 space-y-2 pointer-events-none"></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Toast helper
    window.showToast = function(msg, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        const isSuccess = type === 'success';
        toast.className = `pointer-events-auto px-4 py-3 rounded-xl text-xs font-semibold shadow-2xl flex items-center gap-2 transform transition-all duration-300 translate-y-2 opacity-0 ${isSuccess ? 'bg-slate-900 border border-emerald-500/60 text-emerald-300' : 'bg-slate-900 border border-rose-500/60 text-rose-300'}`;
        toast.innerHTML = `<span>${isSuccess ? '✓' : '⚠'}</span><span>${msg}</span>`;
        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        });

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };
});
</script>
@endpush
</div>
@endsection
