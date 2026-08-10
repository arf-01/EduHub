@extends('layout')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 px-4 relative text-white overflow-hidden">
    <!-- Background glowing blobs -->
    <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-indigo-700 opacity-20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-[400px] h-[400px] bg-purple-600 opacity-20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="relative z-10 bg-gray-900 bg-opacity-90 backdrop-blur-xl shadow-2xl rounded-3xl p-10 max-w-md w-full">
        <h2 class="text-4xl md:text-4xl font-extrabold text-white leading-tight mb-6">
            Login
        </h2>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-8">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold mb-2 text-white">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="you@example.com"
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

            <div class="text-end">
                <a href="{{ route('password.request') }}" class="text-indigo-400 hover:underline font-semibold">
                    Forgot Your Password?
                </a>
            </div>

            <button 
                type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-300 shadow-lg"
            >
                Login
            </button>
        </form>

        <p class="mt-10 text-center text-gray-400 font-medium">
            Don't have an account? 
            <a href="{{ route('registration') }}" class="text-indigo-400 hover:underline font-semibold">
                Register
            </a>
        </p>
    </div>
</div>
@endsection
