@extends('layout')

@section('custom_header')
<header class="bg-gray-900 shadow z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-indigo-400">📘 Quiz System</h1>
        <nav class="space-x-6 text-gray-300 font-medium flex items-center">
            <a href="{{ route('quiz.list') }}" class="hover:text-indigo-400 transition">View Quizzes</a>

            <form method="GET" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" 
                    class="ml-4 text-indigo-300 hover:text-red-400 font-semibold transition">
                    Logout
                </button>
            </form>
        </nav>
    </div>
</header>
@endsection

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 px-4 relative text-white overflow-hidden">
    <!-- Background glowing blobs -->
    <div class="absolute -top-32 -left-32 w-[400px] h-[400px] bg-indigo-700 opacity-20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-[350px] h-[350px] bg-purple-600 opacity-20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="relative z-10 bg-gray-900 bg-opacity-90 backdrop-blur-xl shadow-2xl rounded-3xl p-10 max-w-md w-full">
        <h2 class="text-4xl font-extrabold mb-6">
            Create a New Quiz
        </h2>

        @if (session('success'))
            <div class="mt-4 p-3 bg-green-500 bg-opacity-20 text-green-400 border border-green-500 rounded-lg text-center font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('storeQuiz') }}" method="POST" class="space-y-8">
            @csrf

            <div>
                <label for="quiz_title" class="block text-sm font-semibold mb-2 text-white">Quiz Title</label>
                <input 
                    type="text" 
                    id="quiz_title" 
                    name="quiz_title" 
                    placeholder="Enter quiz title" 
                    required
                    class="w-full px-5 py-3 bg-gray-900 text-white border border-indigo-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-300 shadow-lg transform hover:scale-105"
            >
                Submit Quiz
            </button>
        </form>
    </div>
</div>
@endsection
