@extends('layout')

@section('title', 'My Quizzes — EduHub')
@section('meta_description', 'Manage, view, and analyze all your quizzes from the EduHub dashboard.')
@section('full_bleed', true)

@section('custom_header')
    <x-nav role="teacher" />
@endsection

@section('content')
<div class="relative min-h-[calc(100vh-130px)] px-4 sm:px-6 py-12 overflow-hidden">

    {{-- Background blobs --}}
    <div class="edu-blob edu-animate-blob w-[600px] h-[600px] -top-60 -left-60 bg-indigo-700" style="opacity:.12;"></div>
    <div class="edu-blob edu-animate-blob w-[450px] h-[450px] -bottom-40 -right-40 bg-violet-600" style="opacity:.10; animation-delay:-4s;"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        {{-- Page header --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10 edu-animate-slide-up">
            <div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-1">My Quizzes</h1>
                <p class="text-sm" style="color:var(--edu-text2);">
                    {{ $quizzes->count() }} {{ Str::plural('quiz', $quizzes->count()) }} in your library
                </p>
            </div>
            <a href="{{ route('teacher.view') }}"
               id="create-quiz-btn"
               class="edu-btn-primary shrink-0 px-5 py-2.5 text-sm self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Quiz
            </a>
        </div>

        {{-- Quiz grid --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">

            @forelse ($quizzes as $i => $quiz)
                @php
                    $now       = \Carbon\Carbon::now();
                    $startTime = $quiz->start_datetime ? \Carbon\Carbon::parse($quiz->start_datetime) : null;
                    $durationSec = $quiz->duration ?: ($quiz->questions->count() * 60);
                    $endTime   = $startTime ? $startTime->copy()->addSeconds($durationSec) : null;

                    $status = 'idle';
                    if ($startTime) {
                        if ($now->lt($startTime))               $status = 'scheduled';
                        elseif ($now->between($startTime, $endTime)) $status = 'live';
                        else                                     $status = 'ended';
                    }
                @endphp

                <div id="quiz-row-{{ $quiz->id }}"
                     class="edu-card edu-card-hover relative overflow-hidden edu-animate-slide-up stagger-{{ min($i + 1, 5) }}">

                    {{-- Glow decoration --}}
                    <div class="absolute -top-10 -right-10 w-28 h-28 rounded-full blur-3xl pointer-events-none"
                         style="background: radial-gradient(circle, rgba(99,102,241,0.18) 0%, transparent 70%);"></div>

                    <div class="relative p-6">

                        {{-- Status badge --}}
                        <div class="flex items-center justify-between mb-4">
                            @if ($status === 'live')
                                <span class="edu-badge bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 edu-animate-live">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                                    LIVE
                                </span>
                            @elseif ($status === 'scheduled')
                                <span class="edu-badge bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                    ⏱ Scheduled
                                </span>
                            @elseif ($status === 'ended')
                                <span class="edu-badge" style="background:rgba(51,65,85,0.6); color:#94a3b8; border:1px solid #334155;">
                                    🏁 Ended
                                </span>
                            @else
                                <span class="edu-badge" style="background:rgba(30,45,69,0.8); color:#4b5a72; border:1px solid #1e2d45;">
                                    ⏸ Idle
                                </span>
                            @endif

                            {{-- Question count --}}
                            <span class="text-xs font-mono px-2 py-0.5 rounded-lg" style="background:var(--edu-card2); color:var(--edu-text2); border:1px solid var(--edu-border2);">
                                {{ $quiz->questions->count() }}Q
                            </span>
                        </div>

                        {{-- Title --}}
                        <h2 class="text-lg font-bold text-white mb-1 leading-snug">{{ $quiz->title }}</h2>
                        <p class="text-xs mb-5" style="color:var(--edu-muted);">
                            Created {{ $quiz->created_at->format('M j, Y') }}
                            @if($startTime) · Starts {{ $startTime->format('M j, g:i A') }} @endif
                        </p>

                        {{-- Action buttons --}}
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('quiz.details', $quiz->id) }}"
                               id="view-quiz-{{ $quiz->id }}"
                               class="edu-btn-primary flex-1 text-xs py-2 px-3 min-w-[80px]">
                                ✏️ Manage
                            </a>
                            <a href="{{ route('quiz.leaderboard', $quiz->id) }}"
                               id="leaderboard-quiz-{{ $quiz->id }}"
                               class="edu-btn-ghost flex-1 text-xs py-2 px-3 min-w-[80px] justify-center">
                                🏆 Board
                            </a>
                            <a href="{{ route('quiz.performance', $quiz->id) }}"
                               id="perf-quiz-{{ $quiz->id }}"
                               class="edu-btn-ghost flex-1 text-xs py-2 px-3 min-w-[80px] justify-center"
                               style="color:#c4b5fd; border-color:rgba(139,92,246,0.35);">
                                📊 Stats
                            </a>
                            <button class="edu-btn-ghost text-xs py-2 px-3 delete-btn justify-center"
                                    style="color:#f87171; border-color:rgba(239,68,68,0.3);"
                                    data-id="{{ $quiz->id }}"
                                    id="delete-quiz-{{ $quiz->id }}">
                                🗑
                            </button>
                        </div>
                    </div>
                </div>

            @empty
                <div class="col-span-full">
                    <div class="edu-card flex flex-col items-center justify-center py-24 text-center edu-animate-scale-in">
                        <div class="w-16 h-16 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-3xl mb-4">📝</div>
                        <h3 class="text-lg font-bold text-white mb-2">No Quizzes Yet</h3>
                        <p class="text-sm max-w-sm mb-6" style="color:var(--edu-text2);">
                            Get started by creating your first quiz. Add questions, set a schedule, and share your room name with students.
                        </p>
                        <a href="{{ route('teacher.view') }}" class="edu-btn-primary px-8">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Your First Quiz
                        </a>
                    </div>
                </div>
            @endforelse

        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="delete-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="edu-card max-w-sm w-full p-6 shadow-2xl text-center space-y-4 edu-animate-scale-in">
        <div class="w-12 h-12 mx-auto rounded-2xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-2xl">🗑️</div>
        <div>
            <h3 class="text-lg font-bold text-white mb-1">Delete Quiz?</h3>
            <p class="text-xs" style="color:var(--edu-text2);">This will permanently delete the quiz and all its questions. This action cannot be undone.</p>
        </div>
        <div class="flex gap-3 pt-2">
            <button id="cancel-delete" class="edu-btn-ghost flex-1 text-sm py-2.5">Cancel</button>
            <button id="confirm-delete" class="edu-btn-primary flex-1 text-sm py-2.5" style="background:#ef4444; box-shadow:none;">
                Delete
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const modal      = document.getElementById('delete-modal');
    const cancelBtn  = document.getElementById('cancel-delete');
    const confirmBtn = document.getElementById('confirm-delete');
    let pendingId    = null;

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            pendingId = btn.getAttribute('data-id');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    cancelBtn?.addEventListener('click', () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingId = null;
    });

    confirmBtn?.addEventListener('click', () => {
        if (!pendingId) return;
        fetch(`/quiz/${pendingId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ _method: 'DELETE' })
        })
        .then(r => {
            if (r.ok) {
                const row = document.getElementById(`quiz-row-${pendingId}`);
                if (row) {
                    row.style.transition = 'opacity 0.3s, transform 0.3s';
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.95)';
                    setTimeout(() => row.remove(), 300);
                }
            } else {
                alert('Failed to delete quiz.');
            }
        })
        .catch(() => alert('Something went wrong.'))
        .finally(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingId = null;
        });
    });

    // Close modal on backdrop click
    modal?.addEventListener('click', e => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });
});
</script>
@endpush
@endsection
