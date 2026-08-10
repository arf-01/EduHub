@extends('layout')

@section('custom_header')
<header class="bg-gray-900 shadow z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-indigo-400 tracking-tight">📘 EduHub</h1>
        <nav class="space-x-6 text-gray-300 font-medium">
            <a href="/" class="hover:text-indigo-400 transition">Home</a>
            <a href="#" class="hover:text-indigo-400 transition">Blogs</a>
        </nav>
    </div>
</header>
@endsection

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 relative overflow-hidden py-20 px-6 text-white">
    <!-- Background glowing blobs -->
    <div class="absolute -top-40 -left-40 w-[500px] h-[500px] bg-indigo-700 opacity-20 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-[400px] h-[400px] bg-purple-600 opacity-20 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white leading-tight mb-6">
            📘 Your Quizzes
        </h1>

        <div class="grid gap-8 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($quizzes as $quiz)
                <div id="quiz-row-{{ $quiz->id }}" 
                     class="relative group bg-gray-900/70 border border-indigo-600 backdrop-blur-xl rounded-2xl shadow-2xl p-6 transition-all duration-300 hover:-translate-y-2 hover:ring-2 hover:ring-indigo-500">

                    <!-- Inner blur decoration -->
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-gradient-to-tr from-pink-500 to-purple-700 opacity-20 rounded-full blur-3xl z-0 pointer-events-none"></div>

                    <div class="relative z-10">
                        <h2 class="text-2xl font-semibold text-indigo-300 mb-2">{{ $quiz->title }}</h2>
                        <p class="text-sm text-gray-400 mb-6">🕒 {{ $quiz->created_at->format('d M Y, H:i') }}</p>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('quiz.details', $quiz->id) }}"
                               class="min-w-[100px] bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition duration-200 shadow-lg text-center">
                                View
                            </a>
                            <a href="{{ route('quiz.leaderboard', $quiz->id) }}"
                               class="min-w-[100px] bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-lg transition duration-200 shadow-lg text-center">
                                Leaderboard
                            </a>
                            <a href="{{ route('quiz.performance', $quiz->id) }}"
                               class="min-w-[120px] bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition duration-200 shadow-lg text-center">
                                📊 Performance
                            </a>
                            <button
                                class="min-w-[90px] bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition duration-200 shadow-lg delete-btn text-center"
                                data-id="{{ $quiz->id }}">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-gray-400 italic mt-20 text-lg">
                    No quizzes found.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const quizId = this.getAttribute('data-id');
            if (!confirm('Are you sure you want to delete this quiz?')) return;

            fetch(`/quiz/${quizId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(response => {
                if (response.ok) {
                    document.getElementById(`quiz-row-${quizId}`).remove();
                } else {
                    alert('Failed to delete quiz.');
                }
            })
            .catch(() => alert('Something went wrong.'));
        });
    });
});
</script>
@endpush
