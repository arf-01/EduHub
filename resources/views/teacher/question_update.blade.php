@extends('layout')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-tr from-[#0f0f1a] via-[#1a1a2e] to-[#191825] px-4 py-10">
    <div class="w-full max-w-xl bg-[#1e1e2f] bg-opacity-90 backdrop-blur-xl border border-[#2d2d44] rounded-2xl shadow-2xl p-10 text-white">
        
        <h2 class="text-4xl md:text-5xl font-extrabold text-white leading-tight mb-6">
            Edit Question
        </h2>

        <form action="{{ route('questions.update', $question->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Question Text -->
            <div>
                <label for="text" class="block text-sm font-semibold text-gray-300 mb-2">Question Text</label>
                <textarea
                    id="text"
                    name="text"
                    rows="4"
                    required
                    class="w-full px-5 py-3 bg-[#2c2c42] border border-[#4c4c6d] rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 transition"
                >{{ old('text', $question->text) }}</textarea>
            </div>

            <!-- Options -->
            @for ($i = 1; $i <= 4; $i++)
                <div>
                    <label for="option{{ $i }}" class="block text-sm font-semibold text-gray-300 mb-2">Option {{ $i }}</label>
                    <input
                        type="text"
                        id="option{{ $i }}"
                        name="option{{ $i }}"
                        required
                        value="{{ old('option'.$i, $question->{'option'.$i}) }}"
                        placeholder="Enter option {{ $i }}"
                        class="w-full px-5 py-3 bg-[#2c2c42] border border-[#4c4c6d] rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 transition"
                    >
                </div>
            @endfor

            <!-- Correct Option -->
            <div>
                <label for="right_option" class="text-4xl md:text-3xl font-extrabold text-white leading-tight mb-6">Correct Option</label>
                <select
                    id="right_option"
                    name="right_option"
                    required
                    class="w-full px-5 py-3 bg-[#2c2c42] border border-[#4c4c6d] rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition"
                >
                    <option disabled>Select correct option</option>
                    @for ($i = 1; $i <= 4; $i++)
                        <option value="{{ $i }}" {{ (old('right_option', $question->right_option) == $i) ? 'selected' : '' }}>
                            Option {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Duration -->
            <div>
                <label for="duration" class="block text-sm font-semibold text-gray-300 mb-2">Duration (in seconds)</label>
                <input
                    type="number"
                    id="duration"
                    name="duration"
                    min="1"
                    required
                    value="{{ old('duration', $question->duration) }}"
                    placeholder="Duration"
                    class="w-full px-5 py-3 bg-[#2c2c42] border border-[#4c4c6d] rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 transition"
                >
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-3 rounded-xl shadow-md transform hover:scale-105 transition duration-300 ease-in-out"
            >
                Update Question
            </button>
        </form>
    </div>
</div>
@endsection
