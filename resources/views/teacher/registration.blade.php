@extends('layout')

@section('title', 'Registration')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 px-4 relative text-white overflow-hidden">
    <!-- Background glowing blobs -->
    <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-indigo-700 opacity-20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-[400px] h-[400px] bg-purple-600 opacity-20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="relative z-10 bg-gray-900 bg-opacity-90 backdrop-blur-xl shadow-2xl rounded-3xl p-10 max-w-md w-full">
        <h2 class="text-4xl md:text-4xl font-extrabold text-white leading-tight mb-6">
            Register
        </h2>

        <form action="{{ route('registration.post') }}" method="POST" class="space-y-8">
            @csrf 

            <div>
                <label for="name" class="block text-sm font-semibold mb-2 text-white">Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required 
                    placeholder="Enter your name"
                    class="w-full px-5 py-3 bg-gray-900 text-white border border-indigo-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold mb-2 text-white">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="Enter your email"
                    class="w-full px-5 py-3 bg-gray-900 text-white border border-indigo-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold mb-2 text-white">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="Enter your password"
                    class="w-full px-5 py-3 bg-gray-900 text-white border border-indigo-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:border-indigo-500 transition"
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
                    class="w-full px-5 py-3 bg-gray-900 text-white border border-indigo-600 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-300 shadow-lg"
            >
                Register
            </button>
        </form>

        @if (session('error'))
            <div class="mt-6 p-3 bg-red-500 bg-opacity-20 text-red-400 border border-red-500 rounded-lg text-center font-semibold">
                {{ session('error') }}
            </div>
        @endif
    </div>
</div>
@endsection
