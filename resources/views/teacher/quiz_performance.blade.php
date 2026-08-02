@extends('layout')

@section('custom_header')
<header class="bg-gray-900 shadow z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-indigo-400 tracking-tight">📘 EduHub</h1>
        <nav class="space-x-6 text-gray-300 font-medium">
            <a href="/" class="hover:text-indigo-400 transition">Home</a>
            <a href="{{ route('quiz.listStud') }}" class="hover:text-indigo-400 transition">Quizzes</a>
        </nav>
    </div>
</header>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white px-6 py-20">
    <div class="max-w-5xl mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-extrabold text-indigo-300 mb-10">📊 Student Score Percentage Distribution</h2>

        <div class="bg-gray-900/80 border border-indigo-600 rounded-xl shadow-2xl p-6">
            <canvas id="scoreChart" height="100"></canvas>
        </div>

        <div class="mt-10">
            <a href="{{ route('quiz.leaderboard', $quizId) }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
                🔙 Back to Leaderboard
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Wait for Chart.js to be loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure Chart is available
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }

        const ctx = document.getElementById('scoreChart').getContext('2d');

        const labels = @json(array_keys($buckets));
        const data = @json(array_values($buckets));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Students',
                    data: data,
                    backgroundColor: 'rgba(139, 92, 246, 0.6)', // indigo
                    borderColor: 'rgba(139, 92, 246, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        ticks: { color: '#d1d5db' },
                        grid: { color: '#374151' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#d1d5db', stepSize: 1 },
                        grid: { color: '#374151' }
                    }
                },
                plugins: {
                    legend: { labels: { color: '#d1d5db' } },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#4f46e5',
                        borderWidth: 1
                    }
                }
            }
        });
    });
</script>
@endpush
