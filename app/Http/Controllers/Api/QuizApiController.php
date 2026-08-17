<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Result;
use App\Models\ResultDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizApiController extends Controller
{
    /**
     * Get all quizzes for a room with real-time status (live, scheduled, idle, ended, submitted)
     */
    public function roomQuizzes(Request $request)
    {
        $request->validate([
            'room_name' => 'required|string',
            'student_id' => 'required|string',
        ]);

        $roomName = $request->room_name;
        $studentId = $request->student_id;

        $teacher = User::where('room_name', $roomName)->first();

        if (!$teacher) {
            return response()->json(['error' => 'Room not found. Please verify the room name.'], 404);
        }

        $now = Carbon::now();

        $quizzes = Quiz::where('userid', $teacher->id)
            ->select('id', 'title', 'start_datetime', 'duration', 'created_at', 'userid')
            ->withCount('questions')
            ->where(function ($q) use ($now) {
                // Include: idle (no start), scheduled/live/recently-ended (within last 24 h)
                $q->whereNull('start_datetime')
                  ->orWhere('start_datetime', '>=', $now->copy()->subDay());
            })
            ->orderBy('created_at', 'desc')
            ->limit(20) // safety cap — prevents unbounded growth under load
            ->get();

        $studentResults = Result::whereIn('quiz_id', $quizzes->pluck('id'))
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('quiz_id');

        $quizList = $quizzes->map(function ($quiz) use ($now, $studentResults) {
            $hasSubmitted = isset($studentResults[$quiz->id]);
            $studentResult = $hasSubmitted ? $studentResults[$quiz->id] : null;

            $startTime = $quiz->start_datetime ? Carbon::parse($quiz->start_datetime) : null;
            // duration stores total quiz duration in SECONDS (sum of per-question durations).
            $durationSeconds = max(60, (int)($quiz->duration ?: ($quiz->questions_count * 60)));
            $endTime = $startTime ? $startTime->copy()->addSeconds($durationSeconds) : null;

            // Determine accurate real-time status.
            // A quiz with 0 questions is always idle — it can't be started.
            if ($quiz->questions_count === 0) {
                $status = 'idle';
            } elseif ($hasSubmitted) {
                $status = 'submitted';
            } elseif ($startTime) {
                if ($now->lt($startTime)) {
                    $status = 'scheduled';
                } elseif ($now->between($startTime, $endTime)) {
                    $status = 'live';
                } else {
                    $status = 'ended';
                }
            } else {
                $status = 'idle';
            }

            // server_time is returned once at the root level — omitted here to keep payload lean.
            return [
                'id'             => $quiz->id,
                'title'         => $quiz->title,
                'status'         => $status,
                'question_count' => $quiz->questions_count,
                'duration'       => $durationSeconds,
                'start_datetime' => $startTime ? $startTime->toIso8601String() : null,
                'end_datetime'   => $endTime ? $endTime->toIso8601String() : null,
                'score'          => $studentResult ? $studentResult->score : null,
                'total'          => $quiz->questions_count,
            ];
        });

        return response()->json([
            'teacher_name' => $teacher->name,
            'room_name' => $teacher->room_name,
            'student_id' => $studentId,
            'server_time' => $now->toIso8601String(),
            'quizzes' => $quizList,
        ]);
    }

    /**
     * Start an active/live quiz and fetch its question bank for local IndexedDB caching
     */
    public function start(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'student_id' => 'required|string',
        ]);

        $quizId = $request->quiz_id;
        $studentId = $request->student_id;

        // Check if student already submitted
        $existingResult = Result::where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            ->first();

        if ($existingResult) {
            $quiz = Quiz::withCount('questions')->findOrFail($quizId);
            return response()->json([
                'error' => 'You have already submitted this quiz.',
                'score' => $existingResult->score,
                'total' => $quiz->questions_count,
                'status' => 'submitted'
            ], 400);
        }

        $quiz = Quiz::with(['questions' => function ($query) {
            $query->select(
                'id',
                'quiz_id',
                'text',
                'image',
                'option1',
                'option2',
                'option3',
                'option4',
                'duration'
            );
        }])->findOrFail($quizId);

        $now = Carbon::now();
        $startTime = $quiz->start_datetime ? Carbon::parse($quiz->start_datetime) : null;
        $durationSeconds = max(60, (int)($quiz->duration ?: ($quiz->questions->count() * 60)));
        $endTime = $startTime ? $startTime->copy()->addSeconds($durationSeconds) : null;

        // Verify if quiz is started and not ended
        if (!$startTime) {
            return response()->json(['error' => 'This quiz has not been started by the teacher yet.'], 403);
        }

        if ($now->lt($startTime)) {
            return response()->json([
                'error' => 'This quiz is scheduled and has not started yet.',
                'start_datetime' => $startTime->toIso8601String(),
                'server_time' => $now->toIso8601String()
            ], 403);
        }

        // Allow a 1-minute buffer for clock jitter
        if ($endTime && $now->gt($endTime->copy()->addMinutes(1))) {
            return response()->json(['error' => 'This quiz has already ended.'], 403);
        }

        return response()->json([
            'student_id' => $studentId,
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'duration' => $durationSeconds,
                'start_datetime' => $startTime->toIso8601String(),
            ],
            'questions' => $quiz->questions,
        ]);
    }

    /**
     * Legacy join route kept for backward compatibility
     */
    public function join(Request $request)
    {
        return $this->roomQuizzes($request);
    }


    /**
     * Submit quiz answers
     */
    public function submit(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'student_id' => 'required|string',
            'answers' => 'present|array'
        ]);

        $quizId = $request->quiz_id;
        $studentId = $request->student_id;
        $answers = $request->answers;

        // 1. Check for duplicate submission
        $start = microtime(true);

        $existingResult = Result::where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            ->first();

        $existingResultTime = (microtime(true) - $start) * 1000;

        Log::info('SUBMIT - Existing result query', [
            'time_ms' => round($existingResultTime, 2),
        ]);

        if ($existingResult) {

            $start = microtime(true);

            $quiz = Quiz::withCount('questions')->findOrFail($quizId);

            $quizCountTime = (microtime(true) - $start) * 1000;

            Log::info('SUBMIT - Question count query', [
                'time_ms' => round($quizCountTime, 2),
            ]);

            return response()->json([
                'message' => 'Quiz already submitted.',
                'score' => $existingResult->score,
                'total' => $quiz->questions_count
            ], 200);
        }

        // 2. Load quiz + questions

        $start = microtime(true);

        $quiz = Quiz::with('questions')->findOrFail($quizId);

        $quizQuestionsTime = (microtime(true) - $start) * 1000;

        Log::info('SUBMIT - Quiz + questions query', [
            'time_ms' => round($quizQuestionsTime, 2),
        ]);

        // Grade submission
        $score = 0;
        $correctAnswers = [];

        foreach ($quiz->questions as $question) {
            $correctAnswers[$question->id] = $question;
        }

        $resultDetails = [];

        foreach ($answers as $answer) {
            $questionId = $answer['questionId'] ?? null;
            $selectedOptionIndex = $answer['selectedOption'] ?? null;

            if (!$questionId || !$selectedOptionIndex) {
                continue;
            }

            $question = $correctAnswers[$questionId] ?? null;

            if ($question) {

                if ((int)$selectedOptionIndex === (int)$question->right_option) {
                    $score++;
                }

                $resultDetails[] = [
                    'question_id' => $questionId,
                    'selected_option' => $selectedOptionIndex,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        // 3. Store result inside transaction

        $transactionStart = microtime(true);

        DB::beginTransaction();

        try {

            $start = microtime(true);

            $result = Result::create([
                'student_id' => $studentId,
                'quiz_id' => $quizId,
                'score' => $score
            ]);

            $resultCreateTime = (microtime(true) - $start) * 1000;

            Log::info('SUBMIT - Result create', [
                'time_ms' => round($resultCreateTime, 2),
            ]);

            foreach ($resultDetails as &$detail) {
                $detail['result_id'] = $result->id;
            }

            $start = microtime(true);

            ResultDetail::insert($resultDetails);

            $detailsInsertTime = (microtime(true) - $start) * 1000;

            Log::info('SUBMIT - Result details insert', [
                'time_ms' => round($detailsInsertTime, 2),
            ]);

            $start = microtime(true);

            DB::commit();

            $commitTime = (microtime(true) - $start) * 1000;

            Log::info('SUBMIT - Commit', [
                'time_ms' => round($commitTime, 2),
            ]);

            $totalTransactionTime =
                (microtime(true) - $transactionStart) * 1000;

            Log::info('SUBMIT - TOTAL TRANSACTION', [
                'time_ms' => round($totalTransactionTime, 2),
            ]);

            return response()->json([
                'message' => 'Quiz submitted successfully!',
                'score' => $score,
                'total' => count($quiz->questions)
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => 'Failed to save submission. Please try again.'
            ], 500);
        }
    }
}
