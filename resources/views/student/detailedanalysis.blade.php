@extends('layout')

@section('title', 'Quiz Analysis — EduHub')
@section('meta_description', 'Detailed breakdown of your quiz performance, including per-question analysis.')

@section('custom_header')
    <x-nav role="student" />
@endsection

@section('content')
@php
    $correct   = $details->where('is_correct', true)->count();
    $wrong     = $details->where('selected_option', '!=', 0)->where('is_correct', false)->count();
    $pct       = $total > 0 ? round(($correct / $total) * 100) : 0;
    $circumference = 2 * M_PI * 48; // r=48 → 301.6
    $dashOffset    = $circumference - ($pct / 100) * $circumference;

    // Grade band
    if ($pct >= 90)      { $grade = 'A+'; $gradeColor = '#10b981'; $gradeBg = 'rgba(16,185,129,0.12)'; $gradeBorder = 'rgba(16,185,129,0.35)'; $gradeMsg = 'Outstanding performance!'; }
    elseif ($pct >= 80)  { $grade = 'A';  $gradeColor = '#34d399'; $gradeBg = 'rgba(52,211,153,0.10)'; $gradeBorder = 'rgba(52,211,153,0.3)';  $gradeMsg = 'Excellent work!'; }
    elseif ($pct >= 70)  { $grade = 'B+'; $gradeColor = '#818cf8'; $gradeBg = 'rgba(129,140,248,0.12)'; $gradeBorder = 'rgba(129,140,248,0.3)'; $gradeMsg = 'Good job, keep it up!'; }
    elseif ($pct >= 60)  { $grade = 'B';  $gradeColor = '#a5b4fc'; $gradeBg = 'rgba(165,180,252,0.10)'; $gradeBorder = 'rgba(165,180,252,0.3)'; $gradeMsg = 'Solid effort!'; }
    elseif ($pct >= 50)  { $grade = 'C';  $gradeColor = '#fbbf24'; $gradeBg = 'rgba(251,191,36,0.10)';  $gradeBorder = 'rgba(251,191,36,0.3)';  $gradeMsg = 'You passed, but room to grow.'; }
    elseif ($pct >= 40)  { $grade = 'D';  $gradeColor = '#fb923c'; $gradeBg = 'rgba(251,146,60,0.10)';  $gradeBorder = 'rgba(251,146,60,0.3)';  $gradeMsg = 'More practice recommended.'; }
    else                 { $grade = 'F';  $gradeColor = '#f87171'; $gradeBg = 'rgba(248,113,113,0.10)';  $gradeBorder = 'rgba(248,113,113,0.3)';  $gradeMsg = 'Don\'t give up — review and retry!'; }

    // Ring stroke color
    $ringColor = $gradeColor;
@endphp

<div class="relative min-h-[calc(100vh-130px)] px-4 py-12 overflow-hidden">

    {{-- Background blobs --}}
    <div class="edu-blob edu-animate-blob w-[500px] h-[500px] -top-60 -left-60 bg-indigo-700" style="opacity:.12;"></div>
    <div class="edu-blob edu-animate-blob w-[400px] h-[400px] bottom-0 -right-40 bg-violet-700" style="opacity:.10; animation-delay:-5s;"></div>

    <div class="relative z-10 max-w-4xl mx-auto space-y-8">

        {{-- ── Hero: Score Ring + Grade ── --}}
        <div class="edu-card p-6 sm:p-8 edu-animate-scale-in">
            <div class="flex flex-col sm:flex-row items-center gap-8">

                {{-- SVG Score Ring --}}
                <div class="relative shrink-0">
                    <svg width="140" height="140" viewBox="0 0 140 140" class="rotate-[-90deg]">
                        <circle cx="70" cy="70" r="48" class="edu-ring-track"/>
                        <circle
                            id="score-ring"
                            cx="70" cy="70" r="48"
                            class="edu-ring-fill"
                            stroke="{{ $ringColor }}"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $circumference }}"
                            style="filter: drop-shadow(0 0 8px {{ $ringColor }}60);"
                        />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span id="pct-counter" class="text-3xl font-black text-white" style="font-family:'JetBrains Mono',monospace;">0%</span>
                        <span class="text-xs font-semibold mt-0.5" style="color:var(--edu-text2);">Score</span>
                    </div>
                </div>

                {{-- Info block --}}
                <div class="flex-1 text-center sm:text-left">
                    <div class="flex items-center gap-3 justify-center sm:justify-start mb-3 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Quiz Analysis</h1>
                        {{-- Grade badge --}}
                        <span class="text-xl font-black px-3 py-0.5 rounded-xl border"
                              style="color:{{ $gradeColor }}; background:{{ $gradeBg }}; border-color:{{ $gradeBorder }}; font-family:'JetBrains Mono',monospace;">
                            {{ $grade }}
                        </span>
                    </div>
                    <p class="text-base font-medium mb-6" style="color:{{ $gradeColor }};">{{ $gradeMsg }}</p>

                    {{-- Score summary bar --}}
                    <div class="text-xs font-semibold mb-2 flex justify-between" style="color:var(--edu-text2);">
                        <span>Score breakdown</span>
                        <span class="font-mono text-white">{{ number_format($score, 2) }} pts</span>
                    </div>
                    <div class="edu-progress-track h-2.5 rounded-full overflow-hidden flex">
                        @if($correct > 0)
                        <div class="h-full transition-all duration-700"
                             style="width:{{ round(($correct/$total)*100) }}%; background:var(--edu-green); border-radius:inherit;"></div>
                        @endif
                        @if($wrong > 0)
                        <div class="h-full transition-all duration-700"
                             style="width:{{ round(($wrong/$total)*100) }}%; background:var(--edu-red);"></div>
                        @endif
                        @if($skipped > 0)
                        <div class="h-full transition-all duration-700"
                             style="width:{{ round(($skipped/$total)*100) }}%; background:var(--edu-border2);"></div>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 mt-2.5 text-xs flex-wrap justify-center sm:justify-start">
                        <span class="flex items-center gap-1.5" style="color:var(--edu-text2);">
                            <span class="w-2.5 h-2.5 rounded-sm inline-block" style="background:var(--edu-green);"></span>
                            Correct
                        </span>
                        <span class="flex items-center gap-1.5" style="color:var(--edu-text2);">
                            <span class="w-2.5 h-2.5 rounded-sm inline-block" style="background:var(--edu-red);"></span>
                            Wrong
                        </span>
                        <span class="flex items-center gap-1.5" style="color:var(--edu-text2);">
                            <span class="w-2.5 h-2.5 rounded-sm inline-block" style="background:var(--edu-border2);"></span>
                            Skipped
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Stat Cards Row ── --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 edu-animate-slide-up stagger-1">
            <div class="edu-stat">
                <div class="edu-stat-label flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-slate-500 inline-block"></span>
                    Total
                </div>
                <div class="edu-stat-value">{{ $total }}</div>
                <div class="edu-stat-sub">questions</div>
            </div>
            <div class="edu-stat" style="border-color:rgba(16,185,129,0.35);">
                <div class="edu-stat-label flex items-center gap-1.5" style="color:#10b981;">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    Correct
                </div>
                <div class="edu-stat-value" style="color:#10b981;">{{ $correct }}</div>
                <div class="edu-stat-sub">+1 pt each</div>
            </div>
            <div class="edu-stat" style="border-color:rgba(239,68,68,0.35);">
                <div class="edu-stat-label flex items-center gap-1.5" style="color:#f87171;">
                    <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span>
                    Wrong
                </div>
                <div class="edu-stat-value" style="color:#f87171;">{{ $wrong }}</div>
                <div class="edu-stat-sub">−0.25 pt each</div>
            </div>
            <div class="edu-stat" style="border-color:rgba(99,102,241,0.25);">
                <div class="edu-stat-label flex items-center gap-1.5" style="color:var(--edu-text2);">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>
                    Skipped
                </div>
                <div class="edu-stat-value" style="color:var(--edu-text2);">{{ $skipped }}</div>
                <div class="edu-stat-sub">no penalty</div>
            </div>
        </div>

        {{-- ── Per-Question Accordion ── --}}
        <div class="edu-animate-slide-up stagger-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Per-Question Breakdown
                </h2>
                <div class="flex items-center gap-2 text-xs" style="color:var(--edu-text2);">
                    <button id="expand-all" class="px-3 py-1 rounded-lg border hover:text-white transition"
                            style="border-color:var(--edu-border2);">Expand All</button>
                    <button id="collapse-all" class="px-3 py-1 rounded-lg border hover:text-white transition"
                            style="border-color:var(--edu-border2);">Collapse All</button>
                </div>
            </div>

            <div class="space-y-3" id="questions-container">
                @foreach ($details as $index => $detail)
                    @php
                        $isSkipped = ($detail->selected_option == 0);
                        $isCorrect = (!$isSkipped && $detail->is_correct);
                        $isWrong   = (!$isSkipped && !$detail->is_correct);

                        $statusClass = $isSkipped ? 'skipped' : ($isCorrect ? 'correct' : 'wrong');
                        $statusIcon  = $isSkipped ? '⟳' : ($isCorrect ? '✓' : '✕');
                        $statusText  = $isSkipped ? 'Skipped' : ($isCorrect ? 'Correct' : 'Incorrect');
                        $iconBg      = $isSkipped ? 'rgba(99,102,241,0.15)' : ($isCorrect ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)');
                        $iconColor   = $isSkipped ? '#a5b4fc' : ($isCorrect ? '#10b981' : '#f87171');
                        $badgeBg     = $isSkipped ? 'rgba(99,102,241,0.12)' : ($isCorrect ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.12)');
                        $badgeBorder = $isSkipped ? 'rgba(99,102,241,0.3)' : ($isCorrect ? 'rgba(16,185,129,0.35)' : 'rgba(239,68,68,0.35)');
                        $badgeText   = $isSkipped ? '#a5b4fc' : ($isCorrect ? '#10b981' : '#f87171');

                        $selectedText = null;
                        if (!$isSkipped) {
                            $selectedText = $detail->question->{'option' . $detail->selected_option} ?? 'N/A';
                        }
                        $correctText = $detail->question->{'option' . $detail->question->right_option} ?? 'N/A';
                    @endphp

                    <div class="edu-question-card {{ $statusClass }}" id="q-card-{{ $index }}">

                        {{-- Accordion header --}}
                        <button class="edu-question-header" onclick="toggleQuestion({{ $index }})">
                            {{-- Status icon --}}
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-base font-bold"
                                 style="background:{{ $iconBg }}; color:{{ $iconColor }};">
                                {{ $statusIcon }}
                            </div>

                            {{-- Question number + preview --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-bold uppercase tracking-wider" style="color:var(--edu-text2);">
                                        Q{{ $index + 1 }}
                                    </span>
                                    <span class="edu-badge"
                                          style="background:{{ $badgeBg }}; border:1px solid {{ $badgeBorder }}; color:{{ $badgeText }};">
                                        {{ $statusText }}
                                    </span>
                                </div>
                                @if($detail->question->text)
                                    <p class="text-sm font-medium text-white mt-0.5 truncate">{{ $detail->question->text }}</p>
                                @else
                                    <p class="text-sm text-slate-400 italic mt-0.5">Image question</p>
                                @endif
                            </div>

                            {{-- Chevron --}}
                            <svg id="chevron-{{ $index }}" class="edu-chevron w-4 h-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Accordion body --}}
                        <div class="edu-question-body" id="q-body-{{ $index }}">
                            <div class="pt-4 space-y-4">

                                {{-- Full question text / image --}}
                                @if($detail->question->text)
                                    <p class="text-sm leading-relaxed text-slate-200">{{ $detail->question->text }}</p>
                                @elseif($detail->question->image)
                                    <img src="{{ asset('storage/' . $detail->question->image) }}"
                                         alt="Question image"
                                         class="rounded-xl max-h-52 object-contain border mx-auto"
                                         style="border-color:var(--edu-border2);">
                                @endif

                                {{-- Answer comparison --}}
                                <div class="grid sm:grid-cols-2 gap-3">
                                    {{-- Your answer --}}
                                    <div class="rounded-xl p-3.5 border"
                                         style="background:{{ $isSkipped ? 'rgba(99,102,241,0.05)' : ($isCorrect ? 'rgba(16,185,129,0.06)' : 'rgba(239,68,68,0.06)') }}; border-color:{{ $isSkipped ? 'rgba(99,102,241,0.2)' : ($isCorrect ? 'rgba(16,185,129,0.25)' : 'rgba(239,68,68,0.25)') }};">
                                        <div class="text-xs font-bold uppercase tracking-wider mb-1.5" style="color:var(--edu-muted);">Your Answer</div>
                                        @if($isSkipped)
                                            <p class="text-sm italic" style="color:var(--edu-text2);">— Skipped</p>
                                        @else
                                            <p class="text-sm font-semibold" style="color:{{ $isCorrect ? '#10b981' : '#f87171' }};">
                                                {{ $selectedText }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Correct answer --}}
                                    <div class="rounded-xl p-3.5 border"
                                         style="background:rgba(16,185,129,0.06); border-color:rgba(16,185,129,0.25);">
                                        <div class="text-xs font-bold uppercase tracking-wider mb-1.5" style="color:var(--edu-muted);">Correct Answer</div>
                                        <p class="text-sm font-semibold" style="color:#10b981;">{{ $correctText }}</p>
                                    </div>
                                </div>

                                {{-- Wrong answer guidance --}}
                                @if($isWrong)
                                    <div class="flex items-start gap-2.5 px-3.5 py-3 rounded-xl border"
                                         style="background:rgba(251,191,36,0.06); border-color:rgba(251,191,36,0.2);">
                                        <span class="text-amber-400 text-base mt-0.5">💡</span>
                                        <p class="text-xs leading-relaxed" style="color:#fbbf24;">
                                            Review this question. The correct answer is <strong>{{ $correctText }}</strong>.
                                            Your selected answer was <strong>{{ $selectedText }}</strong>.
                                        </p>
                                    </div>
                                @elseif($isSkipped)
                                    <div class="flex items-start gap-2.5 px-3.5 py-3 rounded-xl border"
                                         style="background:rgba(99,102,241,0.06); border-color:rgba(99,102,241,0.2);">
                                        <span class="text-indigo-400 text-base mt-0.5">📌</span>
                                        <p class="text-xs" style="color:#a5b4fc;">
                                            You skipped this question. The correct answer was <strong>{{ $correctText }}</strong>.
                                        </p>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Back button ── --}}
        <div class="flex justify-center pt-4 pb-8 edu-animate-fade-in">
            <a href="/" class="edu-btn-ghost px-8">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Home
            </a>
        </div>

    </div>{{-- /max-w --}}
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── Animated score ring ──────────────────────────────────
    const ring  = document.getElementById('score-ring');
    const counter = document.getElementById('pct-counter');
    const pct   = {{ $pct }};
    const circ  = {{ $circumference }};
    const targetOffset = circ - (pct / 100) * circ;

    if (ring) {
        requestAnimationFrame(() => {
            ring.style.strokeDashoffset = targetOffset;
        });
    }

    // Animate percentage counter
    if (counter) {
        let current = 0;
        const duration = 1200;
        const step = 16;
        const increment = pct / (duration / step);
        const interval = setInterval(() => {
            current = Math.min(current + increment, pct);
            counter.textContent = Math.round(current) + '%';
            if (current >= pct) clearInterval(interval);
        }, step);
    }

    // ── Accordion helpers ────────────────────────────────────
    window.toggleQuestion = function(index) {
        const body    = document.getElementById('q-body-' + index);
        const chevron = document.getElementById('chevron-' + index);
        if (!body) return;
        const isOpen = body.classList.contains('open');
        body.classList.toggle('open', !isOpen);
        chevron && chevron.classList.toggle('open', !isOpen);
    };

    document.getElementById('expand-all')?.addEventListener('click', () => {
        document.querySelectorAll('.edu-question-body').forEach((el, i) => {
            el.classList.add('open');
            const ch = document.getElementById('chevron-' + i);
            ch && ch.classList.add('open');
        });
    });

    document.getElementById('collapse-all')?.addEventListener('click', () => {
        document.querySelectorAll('.edu-question-body').forEach((el, i) => {
            el.classList.remove('open');
            const ch = document.getElementById('chevron-' + i);
            ch && ch.classList.remove('open');
        });
    });
});
</script>
@endpush
@endsection