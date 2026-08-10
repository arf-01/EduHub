@extends('layout')

@section('title', 'Quiz Result')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-tr from-gray-900 via-gray-800 to-black px-6 py-12">
    <div class="bg-white bg-opacity-10 backdrop-blur-lg shadow-2xl rounded-3xl p-10 max-w-2xl w-full text-white text-center border border-gray-700">
        <h2 class="text-4xl md:text-5xl font-extrabold mb-6 tracking-wide drop-shadow-lg">🎉 Awesome Work!</h2>
        <p class="text-xl md:text-2xl mb-10">You scored <span class="text-green-400 font-bold">{{ $score }}</span> points!</p>

        <div class="mb-10">
            <p class="text-lg text-gray-300 mb-4">Ready to level up? Try our dynamic quiz system — built for teachers and students who want more.</p>
            <div class="flex justify-center gap-4 flex-wrap">
                <span class="bg-gradient-to-r from-purple-500 to-indigo-500 px-4 py-2 rounded-full text-sm font-semibold tracking-wide shadow hover:scale-105 transition">
                    Real-Time Results
                </span>
                <span class="bg-gradient-to-r from-pink-500 to-red-500 px-4 py-2 rounded-full text-sm font-semibold tracking-wide shadow hover:scale-105 transition">
                    Room-based Quiz Access
                </span>
                <span class="bg-gradient-to-r from-cyan-500 to-blue-500 px-4 py-2 rounded-full text-sm font-semibold tracking-wide shadow hover:scale-105 transition">
                    Timer & Violation Detection
                </span>
            </div>
        </div>

        <div class="text-gray-400 italic text-sm">
            Designed for seamless online evaluations. Trusted by educators. Built for the future.
        </div>
    </div>
</div>
@endsection
