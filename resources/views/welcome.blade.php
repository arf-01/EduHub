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
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center relative overflow-hidden px-4">
    <!-- Background blurry shapes -->
    <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-indigo-700 opacity-20 rounded-full blur-[120px]"></div>
    <div class="absolute -bottom-32 -right-32 w-[400px] h-[400px] bg-purple-600 opacity-20 rounded-full blur-[100px]"></div>

    <!-- Content -->
    <div class="relative z-10 max-w-3xl w-full text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold text-white leading-tight mb-6">
            Unlock Your Learning Potential
        </h1>
        <p class="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
            Join EduHub and take quizzes, track progress, and empower your learning. Whether you're a student or a teacher, we’ve got the tools you need.
        </p>

        <div class="flex flex-col md:flex-row justify-center gap-4">
            <form action="{{ route('TorS') }}" method="GET">
                <button name="role" value="student"
                    class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-300 shadow-lg">
                    I'm a Student
                </button>
            </form>

            <form action="{{ route('TorS') }}" method="GET">
                <button name="role" value="teacher"
                    class="w-full md:w-auto bg-gray-800 hover:bg-gray-700 text-indigo-300 border border-indigo-600 font-semibold px-6 py-3 rounded-lg transition duration-300 shadow-lg">
                    I'm a Teacher
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
