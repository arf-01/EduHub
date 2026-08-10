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
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center relative px-4 py-16 overflow-hidden text-white">
    <!-- Blurry glowing background shapes -->
    <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-indigo-700 opacity-20 rounded-full blur-[120px]"></div>
    <div class="absolute -bottom-32 -right-32 w-[400px] h-[400px] bg-purple-600 opacity-20 rounded-full blur-[100px]"></div>

    <!-- Main content -->
    <div class="relative z-10 max-w-6xl w-full">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white text-center mb-12">
            Quizzes for Room: {{ $teacher->room_name }}
        </h1>

        @php
            $dhakaTime = \Carbon\Carbon::now('Asia/Dhaka');
        @endphp

        @if ($quizzes->isEmpty())
            <p class="text-center text-indigo-300 text-lg italic mt-12">No quizzes available in this room.</p>
        @else
            <div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 px-4">
                @foreach ($quizzes as $quiz)
                    @php
                        $startDatetime = \Carbon\Carbon::parse($quiz->start_datetime)->setTimezone('Asia/Dhaka');
                        $endDatetime = $startDatetime->copy()->addSeconds($quiz->duration);


                    @endphp

                    <div class="bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-indigo-500/30 transition-all duration-300">
                        <h2 class="text-2xl font-semibold text-indigo-300 mb-2">{{ $quiz->title }}</h2>
                        

                        <div id="quiz-status-{{ $quiz->id }}"
                             class="quiz-status font-medium"
                             data-id="{{ $quiz->id }}"
                             data-start="{{ $startDatetime->toIso8601String() }}"
                             data-end="{{ $endDatetime->toIso8601String() }}">
                            @if ($dhakaTime->lt($startDatetime))
                                <span class="text-yellow-300">Available on {{ $startDatetime->format('F j, Y, g:i A') }}</span>
                            @elseif ($dhakaTime->gt($endDatetime))
                                <span class="text-red-400">Finished</span>
                            @else
                                <a href="{{ route('quiz.take', ['id' => $quiz->id, 'student_id' => $student_id]) }}"
                                   class="text-green-400 hover:underline">Running</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>



<script>
    /*
    function updateQuizStatus(quizId, startTime, endTime) {
    const now = new Date();
    const start = new Date(startTime);
    const end = new Date(endTime);
    const statusEl = document.getElementById(`quiz-status-${quizId}`);
    const quizLink = `/quiz/${quizId}/take`;

    if (!statusEl) return;

    if (now < start) {
        // Quiz hasn't started yet
        statusEl.innerHTML = `<span class="text-yellow-300">Available on ${start.toLocaleString()}</span>`;
    } else if (now >= start && now < end) {
        // Quiz is running
        statusEl.innerHTML = `<a href="${quizLink}" class="text-green-400 hover:underline">Running</a>`;
    } else {
        // Quiz has finished
        statusEl.innerHTML = `<span class="text-red-400">Finished</span>`;
    }
}








    document.addEventListener("DOMContentLoaded", function () {
      
      const room = "{{ session('room_name') }}";

       if (room) {
        window.Echo.channel(`room.${room}`)
            .listen("QuizStatusUpdated", (e) => {
                
                updateQuizStatus(e.quizId, e.startDatetime, e.endDatetime);
            });
    }
    });*/
</script>
@endsection
