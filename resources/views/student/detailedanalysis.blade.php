@extends('layout')

@section('title', 'Detailed Quiz Result')

@section('content')
<div class="container py-5" style="background-color: #0f172a; min-height: 100vh; color: white; max-width: 100%;"> 
    
    <h1 class="mb-8 text-center text-4xl font-extrabold tracking-tight text-indigo-400">📊 Detailed Quiz Result</h1>

    {{-- Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10 text-center bg-gray-900 rounded-lg shadow p-6 mx-4">
        <div>
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide">Total Questions</h2>
            <p class="text-lime-400 text-2xl font-bold mt-1">{{ $total }}</p>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide">Attempted</h2>
            <p class="text-indigo-400 text-2xl font-bold mt-1">{{ $attempted }}</p>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide">Skipped</h2>
            <p class="text-gray-500 text-2xl font-bold mt-1">{{ $skipped }}</p>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide">Final Score</h2>
            <p class="text-blue-400 text-2xl font-bold mt-1">{{ number_format($score, 2) }}</p>
        </div>
    </div>

    {{-- Per-question analysis --}}
    <h2 class="text-2xl font-semibold mb-5 text-white border-b border-indigo-600 pb-1 flex items-center gap-2 mx-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 0a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>
        Per Question Analysis
    </h2>

    <div class="space-y-4 mx-4">
        @foreach ($details as $index => $detail)
            <div class="rounded-lg border-2 bg-gray-800 border-gray-700 p-4 shadow-md mb-4">
                <p class="text-xs font-semibold text-gray-300 uppercase tracking-wide mb-2">Question {{ $index + 1 }}</p>

                {{-- Question Text / Image --}}
                @if ($detail->question->text)
                    <p class="font-semibold text-md text-white mb-2 leading-snug">{{ $detail->question->text }}</p>
                @elseif ($detail->question->image)
                    <img src="{{ asset('storage/' . $detail->question->image) }}" 
                         alt="Question Image" 
                         class="rounded-md mb-2 max-w-xs max-h-32 object-contain border border-gray-600 shadow-sm mx-auto">
                @else
                    <p class="italic text-gray-500 mb-2 text-sm">No question content available.</p>
                @endif

                {{-- User's Answer --}}
                <p class="mb-1 text-sm">
                    <strong>Your Answer:</strong>
                    @if ($detail->selected_option != 0)
                        @php
                            $selectedOptionText = $detail->question->{'option' . $detail->selected_option} ?? 'N/A';
                        @endphp
                        <span class="text-indigo-300 font-semibold ml-2">{{ $selectedOptionText }}</span>
                    @else
                        <span class="text-gray-400 italic ml-2">Skipped</span>
                    @endif
                </p>

                {{-- Correct Answer (only if skipped or wrong) --}}
                @if ($detail->selected_option == 0 || !$detail->is_correct)
                    <p class="text-sm">
                        <strong>Correct Answer:</strong>
                        @php
                            $correctOptionText = $detail->question->{'option' . $detail->question->right_option} ?? 'N/A';
                        @endphp
                        <span class="text-lime-400 font-semibold ml-2">{{ $correctOptionText }}</span>
                    </p>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection