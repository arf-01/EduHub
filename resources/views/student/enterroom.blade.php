@extends('layout')

@section('title', 'Student Form')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 px-4 relative overflow-hidden text-white">
    <!-- Background glowing blobs -->
    <div class="absolute -top-40 -left-40 w-[400px] h-[400px] bg-indigo-700 opacity-20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-[350px] h-[350px] bg-purple-600 opacity-20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="relative z-10 bg-gray-900 bg-opacity-90 backdrop-blur-xl shadow-xl rounded-3xl p-10 max-w-md w-full">
        <h2 class="text-4xl font-extrabold text-white mb-10 text-center drop-shadow tracking-wide">
            Student Form
        </h2>

        <form action="{{ route('enter.room') }}" method="POST" class="space-y-8">
            @csrf

            <div>
                <label for="student_id" class="block text-sm font-semibold mb-2 text-white">Student ID</label>
                <input 
                    type="text" 
                    id="student_id" 
                    name="student_id" 
                    required 
                    placeholder="Enter your student ID"
                    class="w-full px-5 py-3 bg-gray-900 border border-indigo-600 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
            </div>

            <div>
                <label for="room_name" class="block text-sm font-semibold mb-2 text-white">Room Name</label>
                <input 
                    type="text" 
                    id="room_name" 
                    name="room_name" 
                    required 
                    placeholder="Enter the room name"
                    class="w-full px-5 py-3 bg-gray-900 border border-indigo-600 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-300 shadow-lg"
            >
                Submit
            </button>
        </form>

        @if (session('error'))
            <div class="mt-6 p-4 bg-red-700 bg-opacity-20 text-red-300 rounded-lg text-center font-semibold shadow-inner">
                {{ session('error') }}
            </div>
        @endif
    </div>
</div>
@endsection
