@extends('layout')

@section('title', 'Leaderboard — ' . $quiz->title . ' — EduHub')
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
                <span class="text-slate-200">Leaderboard</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
                    <span>🏆</span>
                    <span>Leaderboard</span>
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-indigo-950/80 text-indigo-300 border border-indigo-700/50 shadow-sm">
                    {{ $quiz->title }}
                </span>
            </div>
            <p class="text-xs text-slate-400">
                {{ $totalParticipants }} {{ Str::plural('student submission', $totalParticipants) }} · {{ $totalQuestions }} {{ Str::plural('question', $totalQuestions) }} in quiz
            </p>
        </div>

        <!-- Quick Navigation & Actions -->
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <a href="{{ route('quiz.details', $quiz->id) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <span>✏️</span> Question Studio
            </a>
            <a href="{{ route('quiz.performance', $quiz->id) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-purple-950/60 hover:bg-purple-900 text-purple-300 border border-purple-800/60 transition flex items-center gap-1.5 shadow-sm">
                <span>📊</span> Score Distribution
            </a>
            @if ($totalParticipants > 0)
                <a href="{{ url('/leaderboard/export/' . $quiz->id) }}" 
                   class="px-3.5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-950/40 transition flex items-center gap-1.5">
                    <span>⬇️</span> Export PDF
                </a>
                <button type="button" id="btn-open-clear-modal"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-950/40 hover:bg-rose-900 text-rose-300 border border-rose-800/40 transition flex items-center gap-1.5">
                    <span>🗑️</span> Reset
                </button>
            @endif
        </div>
    </div>

    <!-- Alert / Toast Messages -->
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-950/60 border border-emerald-700/60 text-emerald-300 text-sm font-semibold flex items-center gap-2 animate-fadeIn">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- 2. Performance Metric Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Submissions -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-lg space-y-1">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
                <span>Submissions</span>
                <span class="text-indigo-400">👥</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-white font-mono">
                {{ $totalParticipants }}
            </div>
            <p class="text-[11px] text-slate-500">Total student completions</p>
        </div>

        <!-- Highest Score -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-lg space-y-1">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
                <span>Top Score</span>
                <span class="text-amber-400">👑</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-amber-300 font-mono flex items-baseline gap-1">
                {{ $highestScore }} <span class="text-xs text-slate-500 font-normal">/ {{ $totalQuestions }}</span>
            </div>
            <p class="text-[11px] text-slate-500">
                {{ $totalQuestions > 0 ? round(($highestScore / $totalQuestions) * 100, 1) : 0 }}% peak score
            </p>
        </div>

        <!-- Class Average -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-lg space-y-1">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
                <span>Class Average</span>
                <span class="text-indigo-400">📈</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-indigo-300 font-mono flex items-baseline gap-1">
                {{ $averageScore }} <span class="text-xs text-slate-500 font-normal">/ {{ $totalQuestions }}</span>
            </div>
            <p class="text-[11px] text-slate-500">
                {{ $totalQuestions > 0 ? round(($averageScore / $totalQuestions) * 100, 1) : 0 }}% mean percentage
            </p>
        </div>

        <!-- Pass Rate -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-lg space-y-1">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase tracking-wider">
                <span>Pass Rate (≥50%)</span>
                <span class="text-emerald-400">🎯</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold {{ $passRate >= 70 ? 'text-emerald-400' : ($passRate >= 50 ? 'text-amber-300' : 'text-rose-400') }} font-mono">
                {{ $passRate }}%
            </div>
            <p class="text-[11px] text-slate-500">Passed threshold</p>
        </div>
    </div>

    <!-- 3. Top 3 Podium Showcase (Rendered when submissions exist) -->
    @if ($totalParticipants >= 1)
        @php
            $firstPlace = $results->get(0);
            $secondPlace = $results->get(1);
            $thirdPlace = $results->get(2);
        @endphp
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
            <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                <span>🏅</span> Top Performers Podium
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <!-- 2nd Place -->
                @if ($secondPlace)
                    @php $pct2 = $totalQuestions > 0 ? round(($secondPlace->score / $totalQuestions) * 100, 1) : 0; @endphp
                    <div class="order-2 md:order-1 bg-slate-950/80 border border-slate-700/70 rounded-2xl p-4 text-center space-y-3 relative overflow-hidden shadow-md">
                        <div class="w-12 h-12 mx-auto rounded-full bg-slate-800 border-2 border-slate-400 text-slate-300 flex items-center justify-center text-xl font-black shadow-inner">
                            🥈
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">2nd Place</span>
                            <h3 class="text-lg font-bold text-white font-mono mt-0.5 truncate">{{ $secondPlace->student_id }}</h3>
                        </div>
                        <div class="bg-slate-900 py-2 px-3 rounded-xl border border-slate-800 inline-block">
                            <span class="text-xl font-extrabold text-slate-200 font-mono">{{ $secondPlace->score }}</span>
                            <span class="text-xs text-slate-400">/ {{ $totalQuestions }}</span>
                            <span class="text-xs font-semibold text-indigo-400 ml-1">({{ $pct2 }}%)</span>
                        </div>
                    </div>
                @else
                    <div class="order-2 md:order-1 hidden md:block"></div>
                @endif

                <!-- 1st Place (Champion) -->
                @if ($firstPlace)
                    @php $pct1 = $totalQuestions > 0 ? round(($firstPlace->score / $totalQuestions) * 100, 1) : 0; @endphp
                    <div class="order-1 md:order-2 bg-gradient-to-b from-amber-950/40 via-slate-900 to-slate-950 border-2 border-amber-500/50 rounded-2xl p-5 text-center space-y-3 relative overflow-hidden shadow-xl transform md:-translate-y-2">
                        <div class="absolute top-0 right-0 left-0 h-1 bg-gradient-to-r from-amber-500 to-yellow-300"></div>
                        <div class="w-14 h-14 mx-auto rounded-full bg-amber-500/20 border-2 border-amber-400 text-amber-300 flex items-center justify-center text-2xl font-black shadow-lg shadow-amber-500/20 animate-pulse">
                            🥇
                        </div>
                        <div>
                            <span class="text-xs font-extrabold text-amber-400 uppercase tracking-widest block">Champion</span>
                            <h3 class="text-xl font-black text-white font-mono mt-0.5 truncate">{{ $firstPlace->student_id }}</h3>
                        </div>
                        <div class="bg-slate-900/90 py-2.5 px-4 rounded-xl border border-amber-500/30 inline-block shadow-inner">
                            <span class="text-2xl font-black text-amber-300 font-mono">{{ $firstPlace->score }}</span>
                            <span class="text-xs text-slate-400">/ {{ $totalQuestions }}</span>
                            <span class="text-xs font-bold text-amber-400 ml-1">({{ $pct1 }}%)</span>
                        </div>
                    </div>
                @endif

                <!-- 3rd Place -->
                @if ($thirdPlace)
                    @php $pct3 = $totalQuestions > 0 ? round(($thirdPlace->score / $totalQuestions) * 100, 1) : 0; @endphp
                    <div class="order-3 bg-slate-950/80 border border-amber-900/50 rounded-2xl p-4 text-center space-y-3 relative overflow-hidden shadow-md">
                        <div class="w-12 h-12 mx-auto rounded-full bg-amber-950/50 border-2 border-amber-700 text-amber-400 flex items-center justify-center text-xl font-black shadow-inner">
                            🥉
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-widest block">3rd Place</span>
                            <h3 class="text-lg font-bold text-white font-mono mt-0.5 truncate">{{ $thirdPlace->student_id }}</h3>
                        </div>
                        <div class="bg-slate-900 py-2 px-3 rounded-xl border border-slate-800 inline-block">
                            <span class="text-xl font-extrabold text-slate-200 font-mono">{{ $thirdPlace->score }}</span>
                            <span class="text-xs text-slate-400">/ {{ $totalQuestions }}</span>
                            <span class="text-xs font-semibold text-indigo-400 ml-1">({{ $pct3 }}%)</span>
                        </div>
                    </div>
                @else
                    <div class="order-3 hidden md:block"></div>
                @endif
            </div>
        </div>
    @endif

    <!-- 4. Full Leaderboard Table Section -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        
        <!-- Table Toolbar -->
        <div class="border-b border-slate-800 px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                    <span>📋 Rankings & Scores</span>
                </h2>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-mono font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                    <span id="filtered-count">{{ $totalParticipants }}</span> Total
                </span>
            </div>

            <!-- Real-time Filter Input -->
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500 text-xs">
                    🔍
                </span>
                <input 
                    type="text" 
                    id="table-search-input" 
                    placeholder="Search by Student ID..." 
                    class="w-full pl-8 pr-3 py-1.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition"
                >
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/60 text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 sm:px-6 w-16">Rank</th>
                        <th class="py-3.5 px-4 sm:px-6">Student ID</th>
                        <th class="py-3.5 px-4 sm:px-6">Score & Accuracy</th>
                        <th class="py-3.5 px-4 sm:px-6 hidden sm:table-cell">Percentage</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Submitted At</th>
                    </tr>
                </thead>
                <tbody id="leaderboard-table-body" class="divide-y divide-slate-800/80">
                    @forelse ($results as $index => $entry)
                        @php
                            $rank = $index + 1;
                            $pct = $totalQuestions > 0 ? round(($entry->score / $totalQuestions) * 100, 1) : 0;
                            $isPassed = $pct >= 50;
                        @endphp
                        <tr class="leaderboard-row hover:bg-slate-800/40 transition duration-150 group" data-student-id="{{ strtolower($entry->student_id) }}">
                            
                            <!-- Rank Column -->
                            <td class="py-3.5 px-4 sm:px-6 font-mono font-bold">
                                @if ($rank === 1)
                                    <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-400/40 flex items-center justify-center text-sm shadow-sm">🥇</span>
                                @elseif ($rank === 2)
                                    <span class="w-7 h-7 rounded-lg bg-slate-500/20 text-slate-300 border border-slate-400/40 flex items-center justify-center text-sm shadow-sm">🥈</span>
                                @elseif ($rank === 3)
                                    <span class="w-7 h-7 rounded-lg bg-amber-900/30 text-amber-400 border border-amber-700/40 flex items-center justify-center text-sm shadow-sm">🥉</span>
                                @else
                                    <span class="text-slate-400 text-sm font-semibold pl-2">#{{ $rank }}</span>
                                @endif
                            </td>

                            <!-- Student ID -->
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-full bg-slate-800 border border-slate-700 text-indigo-400 font-bold text-[11px] flex items-center justify-center">
                                        {{ strtoupper(substr($entry->student_id, 0, 2)) }}
                                    </span>
                                    <span class="font-bold text-white font-mono text-sm tracking-wide">{{ $entry->student_id }}</span>
                                </div>
                            </td>

                            <!-- Score & Bar -->
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="space-y-1.5 max-w-xs">
                                    <div class="flex items-center justify-between font-mono">
                                        <span class="font-bold text-white text-sm">
                                            {{ $entry->score }} <span class="text-xs text-slate-500 font-normal">/ {{ $totalQuestions }}</span>
                                        </span>
                                        <span class="text-xs font-semibold sm:hidden {{ $isPassed ? 'text-emerald-400' : 'text-rose-400' }}">
                                            {{ $pct }}%
                                        </span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $pct >= 80 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-indigo-500' : 'bg-rose-500') }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Percentage Pill (Desktop) -->
                            <td class="py-3.5 px-4 sm:px-6 hidden sm:table-cell">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold font-mono border {{ $pct >= 80 ? 'bg-emerald-950/60 text-emerald-300 border-emerald-800/50' : ($pct >= 50 ? 'bg-indigo-950/60 text-indigo-300 border-indigo-800/50' : 'bg-rose-950/60 text-rose-300 border-rose-800/50') }}">
                                    {{ $pct }}%
                                </span>
                            </td>

                            <!-- Date / Time -->
                            <td class="py-3.5 px-4 sm:px-6 text-right font-mono text-xs text-slate-400">
                                <div>{{ $entry->created_at->format('d M, Y') }}</div>
                                <div class="text-[11px] text-slate-500">{{ $entry->created_at->format('H:i:s') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-state-row">
                            <td colspan="5" class="py-16 text-center text-slate-400 space-y-3">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-2xl">
                                    🏆
                                </div>
                                <h3 class="text-base font-bold text-slate-300">No Submissions Recorded Yet</h3>
                                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                                    When students join using your room name and complete this quiz, their rankings and performance scores will populate here automatically.
                                </p>
                                <div class="pt-2">
                                    <a href="{{ route('quiz.details', $quiz->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-indigo-950/40">
                                        <span>⚙️</span> Manage Quiz Schedule
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    
                    <!-- Search No Results Placeholder -->
                    <tr id="no-search-results" class="hidden">
                        <td colspan="5" class="py-10 text-center text-slate-500 text-xs font-medium">
                            No students match your search query.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Reset / Delete Confirmation Modal -->
<div id="clear-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 text-center transform transition-all">
        <div class="w-12 h-12 mx-auto rounded-full bg-rose-950/80 border border-rose-800 text-rose-400 flex items-center justify-center text-xl">
            🗑️
        </div>
        <div class="space-y-1">
            <h3 class="text-lg font-bold text-white">Reset Leaderboard?</h3>
            <p class="text-xs text-slate-400">
                Are you sure you want to delete all <span class="font-bold text-slate-200">{{ $totalParticipants }} student submissions</span> for <span class="font-bold text-indigo-400">{{ $quiz->title }}</span>? This action cannot be reversed.
            </p>
        </div>
        <form action="{{ route('leaderboard.delete', $quiz->id) }}" method="POST" class="pt-2">
            @csrf
            @method('DELETE')
            <div class="flex items-center gap-3">
                <button type="button" id="btn-cancel-clear" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-rose-950/40">
                    Yes, Delete Submissions
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Search Filtering
    const searchInput = document.getElementById('table-search-input');
    const rows = document.querySelectorAll('.leaderboard-row');
    const filteredCount = document.getElementById('filtered-count');
    const noSearchResults = document.getElementById('no-search-results');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach(row => {
                const sId = row.dataset.studentId || '';
                if (sId.includes(query)) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            if (filteredCount) {
                filteredCount.textContent = visibleCount;
            }

            if (noSearchResults) {
                if (visibleCount === 0 && rows.length > 0) {
                    noSearchResults.classList.remove('hidden');
                } else {
                    noSearchResults.classList.add('hidden');
                }
            }
        });
    }

    // Modal Control
    const clearModal = document.getElementById('clear-modal');
    const btnOpenClearModal = document.getElementById('btn-open-clear-modal');
    const btnCancelClear = document.getElementById('btn-cancel-clear');

    if (btnOpenClearModal && clearModal) {
        btnOpenClearModal.addEventListener('click', () => {
            clearModal.classList.remove('hidden');
            clearModal.classList.add('flex');
        });
    }

    if (btnCancelClear && clearModal) {
        btnCancelClear.addEventListener('click', () => {
            clearModal.classList.add('hidden');
            clearModal.classList.remove('flex');
        });
    }
});
</script>
@endpush
</div>
@endsection
