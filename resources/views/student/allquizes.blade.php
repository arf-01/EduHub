@extends('layout')

@section('title', 'Quizzes — {{ $teacher->room_name }} — EduHub')
@section('meta_description', 'Browse and join quizzes in your assigned room.')

@section('custom_header')
    <x-nav role="student" />
@endsection

@section('content')
<div class="relative min-h-[calc(100vh-130px)] px-4 py-14 overflow-hidden">

    {{-- Background blobs --}}
    <div class="edu-blob edu-animate-blob w-[600px] h-[600px] -top-60 -left-60 bg-indigo-700" style="opacity:.15;"></div>
    <div class="edu-blob edu-animate-blob w-[450px] h-[450px] -bottom-40 -right-40 bg-violet-600" style="opacity:.14; animation-delay:-4s;"></div>

    <div class="relative z-10 max-w-6xl mx-auto">

        {{-- Page header --}}
        <div class="mb-10 edu-animate-slide-up">
            <div class="flex items-center gap-2 text-sm mb-3" style="color:var(--edu-text2);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Room: <span class="font-semibold text-indigo-300">{{ $teacher->room_name }}</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white">Your Quizzes</h1>
            <p class="mt-2 text-sm" style="color:var(--edu-text2);">
                {{ $quizzes->count() }} {{ Str::plural('quiz', $quizzes->count()) }} available in this room
            </p>
        </div>

        @php $dhakaTime = \Carbon\Carbon::now('Asia/Dhaka'); @endphp

        @if ($quizzes->isEmpty())
            {{-- Empty state --}}
            <div class="edu-card flex flex-col items-center justify-center py-20 text-center edu-animate-scale-in">
                <div class="w-16 h-16 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-3xl mb-4">📚</div>
                <h3 class="text-lg font-bold text-white mb-2">No Quizzes Yet</h3>
                <p class="text-sm max-w-sm" style="color:var(--edu-text2);">Your teacher hasn't published any quizzes in this room yet. Check back later!</p>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($quizzes as $i => $quiz)
                    @php
                        $startDatetime = \Carbon\Carbon::parse($quiz->start_datetime)->setTimezone('Asia/Dhaka');
                        $endDatetime   = $startDatetime->copy()->addSeconds($quiz->duration);

                        if ($dhakaTime->lt($startDatetime)) {
                            $quizStatus = 'upcoming';
                        } elseif ($dhakaTime->gt($endDatetime)) {
                            $quizStatus = 'ended';
                        } else {
                            $quizStatus = 'live';
                        }
                    @endphp

                    <div class="edu-card edu-card-hover edu-animate-slide-up relative overflow-hidden stagger-{{ min($i + 1, 5) }}"
                         id="quiz-card-{{ $quiz->id }}">

                        {{-- Accent glow (live only) --}}
                        @if($quizStatus === 'live')
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-emerald-500/5 to-transparent pointer-events-none"></div>
                        @endif

                        {{-- Inner decoration --}}
                        <div class="absolute -top-8 -right-8 w-24 h-24 rounded-full bg-gradient-to-br from-indigo-600/15 to-violet-600/15 blur-2xl pointer-events-none"></div>

                        <div class="relative p-6">
                            {{-- Status badge --}}
                            <div class="flex items-center justify-between mb-4">
                                @if($quizStatus === 'live')
                                    <span class="edu-badge bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 edu-animate-live">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                                        LIVE
                                    </span>
                                @elseif($quizStatus === 'upcoming')
                                    <span class="edu-badge bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                        ⏱ Upcoming
                                    </span>
                                @else
                                    <span class="edu-badge bg-slate-700/60 text-slate-400 border border-slate-700">
                                        🏁 Ended
                                    </span>
                                @endif

                                {{-- Duration badge --}}
                                @if($quiz->duration)
                                    <span class="text-xs font-mono px-2 py-0.5 rounded-lg border" style="background:var(--edu-card2); border-color:var(--edu-border2); color:var(--edu-text2);">
                                        {{ gmdate('i\m', $quiz->duration) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Quiz title --}}
                            <h2 class="text-lg font-bold text-white mb-1 leading-snug">{{ $quiz->title }}</h2>

                            {{-- Start time --}}
                            <p class="text-xs mb-5" style="color:var(--edu-muted);">
                                @if($quizStatus === 'upcoming')
                                    Starts {{ $startDatetime->format('M j, Y · g:i A') }}
                                @elseif($quizStatus === 'live')
                                    Ends {{ $endDatetime->format('g:i A') }}
                                @else
                                    Ended {{ $endDatetime->format('M j, Y · g:i A') }}
                                @endif
                            </p>

                            {{-- CTA --}}
                            <div id="quiz-status-{{ $quiz->id }}"
                                 class="quiz-status"
                                 data-id="{{ $quiz->id }}"
                                 data-start="{{ $startDatetime->toIso8601String() }}"
                                 data-end="{{ $endDatetime->toIso8601String() }}">
                                @if($quizStatus === 'live')
                                    <a href="{{ route('quiz.take', ['id' => $quiz->id, 'student_id' => $student_id]) }}"
                                       id="take-quiz-btn-{{ $quiz->id }}"
                                       class="edu-btn-primary w-full text-sm py-2.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                        Take Quiz
                                    </a>
                                @elseif($quizStatus === 'upcoming')
                                    <div class="w-full text-center py-2.5 rounded-xl text-xs font-semibold border" style="background:var(--edu-card2); border-color:var(--edu-border2); color:var(--edu-text2);">
                                        Available {{ $startDatetime->diffForHumans() }}
                                    </div>
                                @else
                                    <div class="w-full text-center py-2.5 rounded-xl text-xs font-semibold" style="background:rgba(255,255,255,0.04); color:var(--edu-muted);">
                                        Quiz has ended
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
