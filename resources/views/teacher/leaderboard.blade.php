@extends('layout')

@section('custom_header')
<header class="bg-gray-900 shadow z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-indigo-400 tracking-tight">📘 EduHub</h1>
        <nav class="space-x-6 text-gray-300 font-medium">
            <a href="{{ url('/') }}" class="hover:text-indigo-400 transition">Home</a>
            <a href="{{ route('quiz.listStud') }}" class="hover:text-indigo-400 transition">Quizzes</a>
        </nav>
    </div>
</header>
@endsection

@section('title', 'Leaderboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 px-4 py-12 text-white relative overflow-hidden">

    <!-- Background blurry shapes -->
    <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-indigo-700 opacity-20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-[400px] h-[400px] bg-purple-600 opacity-20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-4xl mx-auto space-y-8 relative z-10">

        <h2 class="text-4xl font-extrabold leading-tight mb-6 text-white">
            Leaderboard for Quiz: <span class="text-indigo-400">{{ $quiz->title }}</span>
        </h2>

        <a href="{{ url('/leaderboard/export/' . $quiz->id) }}" 
           class="inline-block px-4 py-2 bg-indigo-600 rounded text-white hover:bg-indigo-700 transition shadow-lg">
           ⬇ Export PDF
        </a>

        <form action="{{ route('leaderboard.delete', $quiz->id) }}" method="POST" class="inline-block ml-3">
    @csrf
    @method('DELETE')
    <button type="submit" 
            class="px-4 py-2 bg-red-600 rounded text-white hover:bg-red-700 transition shadow-lg">
        🗑 Delete Leaderboard
    </button>
</form>

        <div class="bg-gray-900 bg-opacity-80 backdrop-blur-md border border-indigo-700 rounded-3xl shadow-2xl p-6 overflow-x-auto mt-4">

            <table class="min-w-full table-auto border-collapse text-white">
                <thead>
                    <tr class="bg-indigo-600 font-semibold tracking-wide">
                        <th class="px-6 py-4 text-left text-lg rounded-tl-3xl">Rank</th>
                        <th class="px-6 py-4 text-left text-lg">Student ID</th>
                        <th class="px-6 py-4 text-left text-lg">Score</th>
                        <th class="px-6 py-4 text-left text-lg rounded-tr-3xl">Date Taken</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $index => $entry)
                        <tr class="{{ $index % 2 == 0 ? 'bg-gray-800 bg-opacity-30' : 'bg-gray-900 bg-opacity-20' }} hover:bg-purple-700/40 transition-colors duration-300">
                            <td class="px-6 py-3 font-bold text-lg text-purple-300">{{ $index + 1 }}</td>
                            <td class="px-6 py-3 font-semibold">{{ $entry->student_id }}</td>
                            <td class="px-6 py-3 font-bold 
                                {{ $entry->score >= 80 ? 'text-green-400' : ($entry->score >= 50 ? 'text-yellow-300' : 'text-gray-400') }}">
                                {{ $entry->score }}
                            </td>
                            <td class="px-6 py-3 text-gray-300">
                                {{ $entry->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </div>
</div>
@endsection
