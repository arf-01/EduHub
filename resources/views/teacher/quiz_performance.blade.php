@extends('layout')

@section('title', 'Score Distribution — ' . $quiz->title . ' — EduHub')
@section('full_bleed', true)

@section('custom_header')
    <x-nav role="teacher" :quiz-title="$quiz->title" />
@endsection

@section('content')
<div style="background:var(--edu-bg); min-height:calc(100vh - 130px);">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto py-6 sm:py-10 px-4 sm:px-6 space-y-8 text-slate-100">

    <!-- 1. Top Navigation & Header Banner -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('quiz.list') }}" class="hover:text-indigo-400 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Quizzes
                </a>
                <span>/</span>
                <a href="{{ route('quiz.details', $quiz->id) }}" class="hover:text-indigo-400 transition">{{ $quiz->title }}</a>
                <span>/</span>
                <span class="text-slate-200">Score Distribution</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
                    <span>📊</span>
                    <span>Score Percentage Distribution</span>
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-purple-950/80 text-purple-300 border border-purple-700/50 shadow-sm">
                    {{ $quiz->title }}
                </span>
            </div>
            <p class="text-xs text-slate-400">
                Visualizing grade distribution across {{ $totalParticipants }} {{ Str::plural('student', $totalParticipants) }}
            </p>
        </div>

        <!-- Quick Actions Navigation -->
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <a href="{{ route('quiz.details', $quiz->id) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <span>✏️</span> Question Studio
            </a>
            <a href="{{ route('quiz.leaderboard', $quiz->id) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-950/70 hover:bg-indigo-900 text-indigo-300 border border-indigo-800/60 transition flex items-center gap-1.5 shadow-sm">
                <span>🏆</span> Leaderboard
            </a>
        </div>
    </div>

    <!-- 2. Performance Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Participants -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-lg space-y-1">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
                <span>Students</span>
                <span class="text-indigo-400">👥</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-white font-mono">
                {{ $totalParticipants }}
            </div>
            <p class="text-[11px] text-slate-500">Completed test</p>
        </div>

        <!-- Average Score -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-lg space-y-1">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
                <span>Average</span>
                <span class="text-purple-400">📈</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-purple-300 font-mono">
                {{ $averagePercentage }}%
            </div>
            <p class="text-[11px] text-slate-500">{{ $averageScore }} / {{ $totalQuestions }} correct</p>
        </div>

        <!-- Highest Score -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-lg space-y-1">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
                <span>High Score</span>
                <span class="text-emerald-400">🌟</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400 font-mono">
                {{ $highestScore }} <span class="text-xs text-slate-500 font-normal">/ {{ $totalQuestions }}</span>
            </div>
            <p class="text-[11px] text-slate-500">
                {{ $totalQuestions > 0 ? round(($highestScore / $totalQuestions) * 100, 1) : 0 }}% highest
            </p>
        </div>

        <!-- Lowest Score -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-lg space-y-1">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
                <span>Low Score</span>
                <span class="text-slate-400">📉</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-300 font-mono">
                {{ $lowestScore }} <span class="text-xs text-slate-500 font-normal">/ {{ $totalQuestions }}</span>
            </div>
            <p class="text-[11px] text-slate-500">
                {{ $totalQuestions > 0 ? round(($lowestScore / $totalQuestions) * 100, 1) : 0 }}% lowest
            </p>
        </div>

        <!-- Pass Rate -->
        <div class="col-span-2 lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-lg space-y-1">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
                <span>Pass Rate</span>
                <span class="text-indigo-400">🎯</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold {{ $passRate >= 70 ? 'text-emerald-400' : ($passRate >= 50 ? 'text-amber-300' : 'text-rose-400') }} font-mono">
                {{ $passRate }}%
            </div>
            <p class="text-[11px] text-slate-500">≥50% score threshold</p>
        </div>
    </div>

    <!-- 3. Chart & Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Bar Chart Card (8 cols) -->
        <div class="lg:col-span-8 bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
            <div class="flex flex-wrap items-center justify-between border-b border-slate-800 pb-3 gap-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm sm:text-base font-bold text-white tracking-tight flex items-center gap-2">
                        <span>📊 Distribution Frequency Histogram</span>
                    </h2>
                </div>
                <span class="text-[11px] font-mono text-slate-400 bg-slate-800 px-2.5 py-0.5 rounded-full border border-slate-700">
                    Grade Bands
                </span>
            </div>

            @if ($totalParticipants > 0)
                <div class="relative w-full h-72 sm:h-80 pt-2">
                    <canvas id="scoreChart"></canvas>
                </div>
            @else
                <div class="py-16 text-center text-slate-400 space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-2xl">
                        📊
                    </div>
                    <h3 class="text-base font-bold text-slate-300">No Student Submissions Yet</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Once students start submitting this quiz, score percentages will be automatically plotted on this histogram in real-time.
                    </p>
                </div>
            @endif
        </div>

        <!-- Grade Band Breakdown Card (4 cols) -->
        <div class="lg:col-span-4 bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="border-b border-slate-800 pb-3">
                    <h2 class="text-sm sm:text-base font-bold text-white tracking-tight flex items-center gap-2">
                        <span>📑 Performance Brackets</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Cohort breakdown by score range</p>
                </div>

                <div class="space-y-3">
                    @php
                        $bracketColors = [
                            '100%' => ['bg' => 'bg-emerald-500', 'badge' => 'text-emerald-300 border-emerald-800/50 bg-emerald-950/60', 'name' => 'Perfect Score (100%)'],
                            '>80%' => ['bg' => 'bg-indigo-500', 'badge' => 'text-indigo-300 border-indigo-800/50 bg-indigo-950/60', 'name' => 'Distinction (>80%)'],
                            '>60%' => ['bg' => 'bg-blue-500', 'badge' => 'text-blue-300 border-blue-800/50 bg-blue-950/60', 'name' => 'Proficient (>60%)'],
                            '>40%' => ['bg' => 'bg-amber-500', 'badge' => 'text-amber-300 border-amber-800/50 bg-amber-950/60', 'name' => 'Average (>40%)'],
                            '<=40%' => ['bg' => 'bg-rose-500', 'badge' => 'text-rose-300 border-rose-800/50 bg-rose-950/60', 'name' => 'Needs Attention (≤40%)'],
                        ];
                    @endphp

                    @foreach ($buckets as $bracket => $count)
                        @php
                            $bracketPct = $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 1) : 0;
                            $meta = $bracketColors[$bracket] ?? ['bg' => 'bg-indigo-500', 'badge' => 'text-slate-300 border-slate-700 bg-slate-800', 'name' => $bracket];
                        @endphp
                        <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-3 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-200">{{ $meta['name'] }}</span>
                                <span class="font-mono font-bold px-2 py-0.5 rounded-full border text-[11px] {{ $meta['badge'] }}">
                                    {{ $count }} {{ Str::plural('student', $count) }} · {{ $bracketPct }}%
                                </span>
                            </div>
                            <!-- Bar -->
                            <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="h-full rounded-full {{ $meta['bg'] }} transition-all duration-500" style="width: {{ $bracketPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex justify-between items-center text-xs text-slate-500">
                <span>Auto-computed from quiz results</span>
                <a href="{{ route('quiz.leaderboard', $quiz->id) }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">View Leaderboard →</a>
            </div>
        </div>

    </div>

</div>

@push('scripts')
@if ($totalParticipants > 0)
<script>
document.addEventListener('DOMContentLoaded', () => {
    function initChart() {
        if (typeof Chart === 'undefined') {
            setTimeout(initChart, 50);
            return;
        }

        const ctx = document.getElementById('scoreChart');
        if (!ctx) return;

        const labels = @json(array_keys($buckets));
        const dataValues = @json(array_values($buckets));

        // Format labels nicely
        const cleanLabels = labels.map(l => {
            if (l === '100%') return '100% (Perfect)';
            if (l === '>80%') return '81% - 99%';
            if (l === '>60%') return '61% - 80%';
            if (l === '>40%') return '41% - 60%';
            if (l === '<=40%') return '0% - 40%';
            return l;
        });

        // Chart gradient colors
        const chartCtx = ctx.getContext('2d');
        const gradient = chartCtx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, '#6366f1');
        gradient.addColorStop(1, '#a855f7');

        new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: cleanLabels,
                datasets: [{
                    label: 'Number of Students',
                    data: dataValues,
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.75)', // 100% emerald
                        'rgba(99, 102, 241, 0.75)', // >80% indigo
                        'rgba(59, 130, 246, 0.75)', // >60% blue
                        'rgba(245, 158, 11, 0.75)', // >40% amber
                        'rgba(239, 68, 68, 0.75)',  // <=40% rose
                    ],
                    borderColor: [
                        '#10b981',
                        '#6366f1',
                        '#3b82f6',
                        '#f59e0b',
                        '#ef4444',
                    ],
                    borderWidth: 1.5,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 56
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                const count = context.raw || 0;
                                const total = {{ $totalParticipants }};
                                const pct = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
                                return `Students: ${count} (${pct}% of class)`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                family: "'Inter', sans-serif",
                                size: 11,
                                weight: '600'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(51, 65, 85, 0.4)'
                        },
                        ticks: {
                            color: '#94a3b8',
                            stepSize: 1,
                            precision: 0,
                            font: {
                                family: "'JetBrains Mono', monospace",
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }

    initChart();
});
</script>
@endif
@endpush
</div>
@endsection
