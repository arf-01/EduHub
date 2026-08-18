@extends('layout')

@section('title', $quiz->title . ' — Quiz Management — EduHub')
@section('full_bleed', true)

@section('custom_header')
    <x-nav role="teacher" :quiz-title="$quiz->title" />
@endsection

@section('content')
<div style="background:var(--edu-bg); min-height:calc(100vh - 130px);">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto py-6 sm:py-10 px-4 sm:px-6 space-y-8 text-slate-100">

    <!-- 1. Top Navigation & Quiz Overview Banner -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('quiz.list') }}" class="hover:text-indigo-400 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Quizzes
                </a>
                <span>/</span>
                <span class="text-slate-200">Management</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $quiz->title }}</h1>
                <span id="quiz-question-count-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-indigo-950/80 text-indigo-300 border border-indigo-700/50 shadow-sm">
                    {{ $quiz->questions->count() }} {{ Str::plural('Question', $quiz->questions->count()) }}
                </span>
            </div>
            <p class="text-xs text-slate-400">
                Created: {{ $quiz->created_at->format('d M, Y · H:i') }}
            </p>
        </div>

        <!-- Quick Actions Navigation -->
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <a href="{{ route('quiz.performance', $quiz->id) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <span>📊</span> Performance
            </a>
            <a href="{{ route('quiz.leaderboard', $quiz->id) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <span>🏆</span> Leaderboard
            </a>
            <a href="#schedule-section" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-950/70 hover:bg-indigo-900 text-indigo-300 border border-indigo-800/60 transition flex items-center gap-1.5 shadow-sm">
                <span>⏱️</span> Schedule / Start
            </a>
        </div>
    </div>

    <!-- 2. Question Studio (Add Question) -->
    <div id="add-question-section" class="space-y-3">
        <x-question-studio.editor :quiz="$quiz" />
    </div>

    <!-- 3. Existing Questions List -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="border-b border-slate-800 px-5 sm:px-6 py-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                    <span>📋 Question Bank</span>
                    <span id="list-count-badge" class="text-xs px-2 py-0.5 rounded-full font-mono bg-slate-800 text-slate-300 border border-slate-700">
                        {{ $quiz->questions->count() }}
                    </span>
                </h2>
            </div>
            <a href="#add-question-section" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1">
                <span>+ Add Another Question</span>
            </a>
        </div>

        <!-- Dynamic Questions Body -->
        <div id="questions-list-container" class="divide-y divide-slate-800">
            @forelse ($quiz->questions as $question)
                <div class="question-row p-4 sm:p-6 hover:bg-slate-800/40 transition duration-150 group" id="question-row-{{ $question->id }}" data-id="{{ $question->id }}">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                        
                        <!-- Question Content & Meta -->
                        <div class="space-y-3 w-full md:max-w-3xl">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-md bg-slate-800 border border-slate-700 text-indigo-400 font-bold text-xs flex items-center justify-center font-mono">
                                    {{ $loop->iteration }}
                                </span>
                                <span class="text-xs font-mono font-medium bg-slate-800/80 text-slate-400 px-2 py-0.5 rounded border border-slate-700/60">
                                    ⏱️ {{ $question->duration ?? 60 }}s
                                </span>
                            </div>

                            <!-- Question Text / Code Rendering -->
                            <div class="text-sm sm:text-base text-slate-200 font-medium leading-relaxed">
                                @if ($question->text)
                                    <div class="rendered-question-text" data-raw-text="{{ $question->text }}">
                                        {{ $question->text }}
                                    </div>
                                @endif
                            </div>

                            <!-- Multiple Choice Options Pills -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2">
                                @foreach([1 => $question->option1, 2 => $question->option2, 3 => $question->option3, 4 => $question->option4] as $idx => $opt)
                                    @php $isCorrect = ($idx == $question->right_option); @endphp
                                    <div class="px-3 py-2 rounded-xl text-xs flex items-center justify-between border {{ $isCorrect ? 'bg-emerald-950/30 border-emerald-600/60 text-emerald-300 font-bold shadow-sm' : 'bg-slate-950/50 border-slate-800 text-slate-400' }}">
                                        <span class="flex items-center gap-2 truncate">
                                            <span class="w-4 h-4 rounded-full text-[10px] flex items-center justify-center font-black {{ $isCorrect ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-500' }}">
                                                {{ chr(64 + $idx) }}
                                            </span>
                                            <span class="opt-label-render truncate" data-raw="{{ $opt }}">{{ $opt }}</span>
                                        </span>
                                        @if ($isCorrect)
                                            <span class="text-[10px] text-emerald-400 font-bold ml-2 shrink-0">✓ Correct</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 self-end md:self-start pt-2 md:pt-0">
                            <a href="{{ route('questions.edit', $question->id) }}" 
                               class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                                <span>✏️</span> Edit
                            </a>
                            <button type="button" 
                                    class="btn-trigger-delete px-3 py-1.5 bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border border-rose-800/40 rounded-lg text-xs font-semibold transition flex items-center gap-1" 
                                    data-id="{{ $question->id }}"
                                    data-num="{{ $loop->iteration }}">
                                <span>🗑️</span> Delete
                            </button>
                        </div>

                    </div>
                </div>
            @empty
                <div id="no-questions-placeholder" class="p-12 text-center text-slate-400 space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-800 flex items-center justify-center text-2xl">
                        📝
                    </div>
                    <h3 class="text-base font-bold text-slate-300">No Questions Added Yet</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">
                        Use the Question Studio above to start creating your first question with text, code snippets, or images.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 4. Schedule / Start Quiz Section -->
    <div id="schedule-section" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="border-b border-slate-800 px-5 sm:px-6 py-4 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                <span>⏱️ Schedule & Release Quiz</span>
            </h3>
            
            <!-- Dynamic Status Badge -->
            @php
                $now = \Carbon\Carbon::now();
                $startTime = $quiz->start_datetime ? \Carbon\Carbon::parse($quiz->start_datetime) : null;
                $durationSec = $quiz->duration ?: ($quiz->questions->count() * 60);
                $endTime = $startTime ? $startTime->copy()->addSeconds($durationSec) : null;
                
                $quizStatus = 'idle';
                if ($startTime) {
                    if ($now->lt($startTime)) {
                        $quizStatus = 'scheduled';
                    } elseif ($now->between($startTime, $endTime)) {
                        $quizStatus = 'live';
                    } else {
                        $quizStatus = 'ended';
                    }
                }
            @endphp

            @if ($quizStatus === 'live')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-500/60 flex items-center gap-1.5 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> LIVE NOW (Closes at {{ $endTime->format('H:i') }})
                </span>
            @elseif ($quizStatus === 'scheduled')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-950/80 text-amber-300 border border-amber-500/60 flex items-center gap-1.5">
                    <span>⏱️</span> Scheduled for {{ $startTime->format('d M, H:i') }}
                </span>
            @elseif ($quizStatus === 'ended')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700 flex items-center gap-1.5">
                    <span>🏁</span> Quiz Ended
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-400 border border-slate-700">
                    ⏸️ Not Started (Idle)
                </span>
            @endif
        </div>

        <div class="p-5 sm:p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Schedule for later -->
                <form action="{{ route('quiz.schedule', $quiz->id) }}" method="POST" class="space-y-3 bg-slate-950/60 p-4 rounded-xl border border-slate-800 flex flex-col justify-between">
                    @csrf
                    <div>
                        <label for="start_datetime" class="block text-xs font-bold text-slate-300 uppercase tracking-wider cursor-pointer">
                            Set Scheduled Date & Time
                        </label>
                        <p class="text-xs text-slate-400 mt-1">Students will see a live countdown in their room lobby.</p>
                    </div>
                    <div class="space-y-2 pt-2">
                        <input type="text" id="start_datetime" name="start_datetime" placeholder="Choose date and time (e.g. 2026-08-17 14:00)" value="{{ $quiz->start_datetime ? \Carbon\Carbon::parse($quiz->start_datetime)->format('Y-m-d H:i') : '' }}" class="w-full rounded-xl bg-slate-900 text-white border border-slate-700 px-3.5 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none cursor-pointer">
                        <div class="flex items-center gap-2">
                            <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition">
                                Save Schedule
                            </button>
                            @if ($quiz->start_datetime)
                                <button type="button" onclick="document.getElementById('start_datetime').value = ''; this.form.submit();" class="px-3 py-2 bg-slate-800 hover:bg-rose-900/50 hover:border-rose-700 text-slate-300 hover:text-rose-200 rounded-xl text-xs font-bold transition border border-slate-700" title="Clear Scheduled Time">
                                    Clear
                                </button>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- Instant Start -->
                <div class="bg-slate-950/60 p-4 rounded-xl border border-slate-800 flex flex-col justify-between gap-3">
                    <div>
                        <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Instant Start</h4>
                        <p class="text-xs text-slate-400 mt-1">Make this quiz live for all students immediately.</p>
                    </div>
                    <form action="{{ route('quiz.startnow', $quiz->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-emerald-950/40">
                            🚀 Start Quiz Now
                        </button>
                    </form>
                </div>

                <!-- Emergency / Manual End -->
                <div class="bg-slate-950/60 p-4 rounded-xl border border-slate-800 flex flex-col justify-between gap-3">
                    <div>
                        <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Close Quiz</h4>
                        <p class="text-xs text-slate-400 mt-1">Immediately close exam window and finalize submissions.</p>
                    </div>
                    <form action="{{ route('quiz.endnow', $quiz->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to end this quiz now? Students will no longer be able to start.');">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-rose-950/40">
                            🛑 End Quiz Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Destructive Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 text-center transform transition-all">
        <div class="w-12 h-12 mx-auto rounded-full bg-rose-950/80 border border-rose-800 text-rose-400 flex items-center justify-center text-xl">
            🗑️
        </div>
        <div class="space-y-1">
            <h3 class="text-lg font-bold text-white">Delete Question?</h3>
            <p class="text-xs text-slate-400">
                Are you sure you want to remove <span id="delete-modal-qnum" class="font-bold text-slate-200">this question</span> from the quiz? This action cannot be undone.
            </p>
        </div>
        <div class="flex items-center gap-3 pt-2">
            <button type="button" id="btn-cancel-delete" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition">
                Cancel
            </button>
            <button type="button" id="btn-confirm-delete" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-rose-950/40">
                Delete Question
            </button>
        </div>
    </div>
</div>

<!-- Floating Toast Container -->
<div id="toast-container" class="fixed bottom-5 right-5 z-50 space-y-2 pointer-events-none"></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const listContainer = document.getElementById('questions-list-container');
    const countBadge = document.getElementById('quiz-question-count-badge');
    const listCountBadge = document.getElementById('list-count-badge');

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

    // Render questions code blocks & inline code in list
    function renderQuestionListCode() {
        const highlighter = window.QuizHighlighter;
        if (!highlighter) return;

        // Render question text code blocks
        document.querySelectorAll('.rendered-question-text').forEach(el => {
            const raw = el.dataset.rawText || el.textContent;
            if (!raw) return;

            const blocks = highlighter.parseContentWithCode(raw);
            if (blocks.length > 0) {
                let html = '';
                blocks.forEach(b => {
                    if (b.type === 'code') {
                        html += highlighter.renderCodeBoxHtml(b.content, b.language);
                    } else {
                        html += `<p class="whitespace-pre-wrap">${highlighter.formatInlineCode(highlighter.escapeHtml(b.content.trim()))}</p>`;
                    }
                });
                el.innerHTML = html;
            } else {
                el.innerHTML = highlighter.formatInlineCode(highlighter.escapeHtml(raw));
            }
        });

        // Render inline code in option pills
        document.querySelectorAll('.opt-label-render').forEach(el => {
            const raw = el.dataset.raw || el.textContent;
            el.innerHTML = highlighter.formatInlineCode(highlighter.escapeHtml(raw));
        });
    }

    renderQuestionListCode();

    // Listen for new questions added via Question Studio
    window.addEventListener('question-added', (e) => {
        const q = e.detail;
        if (!q || !q.id) return;

        document.getElementById('no-questions-placeholder')?.remove();

        const currentCount = document.querySelectorAll('.question-row').length + 1;
        const highlighter = window.QuizHighlighter || {
            parseContentWithCode: () => [],
            formatInlineCode: (s) => s,
            renderCodeBoxHtml: (c) => `<pre class="bg-slate-900 text-emerald-400 p-2 rounded text-xs font-mono">${c}</pre>`,
            escapeHtml: (s) => s
        };

        // Render text/code
        let contentHtml = '';
        if (q.text) {
            const blocks = highlighter.parseContentWithCode(q.text);
            if (blocks.length > 0) {
                blocks.forEach(b => {
                    if (b.type === 'code') {
                        contentHtml += highlighter.renderCodeBoxHtml(b.content, b.language);
                    } else {
                        contentHtml += `<p class="whitespace-pre-wrap">${highlighter.formatInlineCode(highlighter.escapeHtml(b.content.trim()))}</p>`;
                    }
                });
            } else {
                contentHtml = `<p class="whitespace-pre-wrap">${highlighter.formatInlineCode(highlighter.escapeHtml(q.text))}</p>`;
            }
        }

        const options = [q.option1, q.option2, q.option3, q.option4];
        let optionsHtml = '';
        options.forEach((opt, idx) => {
            const i = idx + 1;
            const isCorrect = (i == q.right_option);
            optionsHtml += `
                <div class="px-3 py-2 rounded-xl text-xs flex items-center justify-between border ${isCorrect ? 'bg-emerald-950/30 border-emerald-600/60 text-emerald-300 font-bold shadow-sm' : 'bg-slate-950/50 border-slate-800 text-slate-400'}">
                    <span class="flex items-center gap-2 truncate">
                        <span class="w-4 h-4 rounded-full text-[10px] flex items-center justify-center font-black ${isCorrect ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-500'}">
                            ${String.fromCharCode(64 + i)}
                        </span>
                        <span class="truncate">${highlighter.formatInlineCode(highlighter.escapeHtml(opt))}</span>
                    </span>
                    ${isCorrect ? '<span class="text-[10px] text-emerald-400 font-bold ml-2 shrink-0">✓ Correct</span>' : ''}
                </div>
            `;
        });

        const row = document.createElement('div');
        row.id = `question-row-${q.id}`;
        row.dataset.id = q.id;
        row.className = "question-row p-4 sm:p-6 hover:bg-slate-800/40 transition duration-150 group animate__animated animate__fadeInDown";
        row.innerHTML = `
            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                <div class="space-y-3 w-full md:max-w-3xl">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-md bg-slate-800 border border-slate-700 text-indigo-400 font-bold text-xs flex items-center justify-center font-mono">
                            ${currentCount}
                        </span>
                        <span class="text-xs font-mono font-medium bg-slate-800/80 text-slate-400 px-2 py-0.5 rounded border border-slate-700/60">
                            ⏱️ ${q.duration || 60}s
                        </span>
                    </div>
                    <div class="text-sm sm:text-base text-slate-200 font-medium leading-relaxed">
                        ${contentHtml}
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2">
                        ${optionsHtml}
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 self-end md:self-start pt-2 md:pt-0">
                    <a href="/questions/edit/${q.id}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                        <span>✏️</span> Edit
                    </a>
                    <button type="button" class="btn-trigger-delete px-3 py-1.5 bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border border-rose-800/40 rounded-lg text-xs font-semibold transition flex items-center gap-1" data-id="${q.id}" data-num="${currentCount}">
                        <span>🗑️</span> Delete
                    </button>
                </div>
            </div>
        `;

        listContainer.appendChild(row);

        // Update counts
        const newTotal = document.querySelectorAll('.question-row').length;
        countBadge.textContent = `${newTotal} ${newTotal === 1 ? 'Question' : 'Questions'}`;
        listCountBadge.textContent = newTotal;
    });

    // Destructive Delete Confirmation Modal Handling
    const deleteModal = document.getElementById('delete-modal');
    const deleteModalQNum = document.getElementById('delete-modal-qnum');
    const btnCancelDelete = document.getElementById('btn-cancel-delete');
    const btnConfirmDelete = document.getElementById('btn-confirm-delete');
    let targetDeleteId = null;

    document.addEventListener('click', (e) => {
        const delBtn = e.target.closest('.btn-trigger-delete');
        if (delBtn) {
            targetDeleteId = delBtn.dataset.id;
            deleteModalQNum.textContent = `Question #${delBtn.dataset.num || ''}`;
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
        }
    });

    btnCancelDelete.addEventListener('click', () => {
        targetDeleteId = null;
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
    });

    btnConfirmDelete.addEventListener('click', async () => {
        if (!targetDeleteId) return;

        btnConfirmDelete.disabled = true;
        btnConfirmDelete.textContent = 'Deleting...';

        try {
            const res = await fetch(`/questions/${targetDeleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                const targetRow = document.getElementById(`question-row-${targetDeleteId}`);
                if (targetRow) {
                    targetRow.classList.add('animate__animated', 'animate__fadeOut');
                    setTimeout(() => {
                        targetRow.remove();
                        const remaining = document.querySelectorAll('.question-row').length;
                        countBadge.textContent = `${remaining} ${remaining === 1 ? 'Question' : 'Questions'}`;
                        listCountBadge.textContent = remaining;
                        if (remaining === 0) {
                            listContainer.innerHTML = `
                                <div id="no-questions-placeholder" class="p-12 text-center text-slate-400 space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-800 flex items-center justify-center text-2xl">📝</div>
                                    <h3 class="text-base font-bold text-slate-300">No Questions Added Yet</h3>
                                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Use the Question Studio above to start creating your first question.</p>
                                </div>
                            `;
                        }
                    }, 300);
                }
                window.showToast('Question deleted successfully.', 'success');
            } else {
                alert('Failed to delete question.');
            }
        } catch (err) {
            console.error("Delete error:", err);
            alert('An error occurred during deletion.');
        } finally {
            btnConfirmDelete.disabled = false;
            btnConfirmDelete.textContent = 'Delete Question';
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
            targetDeleteId = null;
        }
    });

    // Flatpickr for Quiz Scheduling with robust initialization
    function initFlatpickr(attempts = 0) {
        if (typeof window.flatpickr !== 'undefined') {
            window.flatpickr("#start_datetime", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                allowInput: true,
                minuteIncrement: 1
            });
        } else if (attempts < 20) {
            setTimeout(() => initFlatpickr(attempts + 1), 100);
        }
    }
    initFlatpickr();

    // Session flash toasts
    @if (session('success'))
        window.showToast(@json(session('success')), 'success');
    @endif
    @if (session('error'))
        window.showToast(@json(session('error')), 'error');
    @endif
});
</script>
@endpush
</div>
@endsection