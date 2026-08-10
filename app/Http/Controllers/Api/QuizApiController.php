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
     * Join a quiz room
     */
    public function join(Request $request)
    {
        $totalStart = microtime(true);
       

        $request->validate([
            'room_name' => 'required|string',
            'student_id' => 'required|string',
        ]);

        $validationTime = microtime(true);

        $roomName = $request->room_name;
        $studentId = $request->student_id;

        // ============================================================
        // 1. Find the teacher
        // ============================================================

        $start = microtime(true);

        $teacher = User::where('room_name', $roomName)->first();

        $teacherQueryTime = (microtime(true) - $start) * 1000;

        Log::info('JOIN - Teacher query', [
            'time_ms' => round($teacherQueryTime, 2),
        ]);

        if (!$teacher) {
            return response()->json(['error' => 'Room not found.'], 404);
        }

        // ============================================================
        // 2. Find the active quiz + questions
        // ============================================================

        $now = Carbon::now();

        $start = microtime(true);

        $activeQuiz = Quiz::where('userid', $teacher->id)
            ->where('start_datetime', '<=', $now)
            ->whereRaw(
                'DATE_ADD(start_datetime, INTERVAL duration MINUTE) >= ?',
                [$now]
            )
            ->orderBy('start_datetime', 'desc')
            ->with(['questions' => function ($query) {
                $query->select(
                    'id',
                    'quiz_id',
                    'text',
                    'image',
                    'option1',
                    'option2',
                    'option3',
                    'option4'
                );
            }])
            ->first();

        $activeQuizQueryTime = (microtime(true) - $start) * 1000;

        Log::info('JOIN - Active quiz + questions', [
            'time_ms' => round($activeQuizQueryTime, 2),
        ]);

        // ============================================================
        // Fallback: get latest quiz if no active quiz found
        // ============================================================

        $fallbackQueryTime = 0;

        if (!$activeQuiz) {

            $start = microtime(true);

            $activeQuiz = Quiz::where('userid', $teacher->id)
                ->orderBy('start_datetime', 'desc')
                ->with(['questions' => function ($query) {
                    $query->select(
                        'id',
                        'quiz_id',
                        'text',
                        'image',
                        'option1',
                        'option2',
                        'option3',
                        'option4'
                    );
                }])
                ->first();

            $fallbackQueryTime = (microtime(true) - $start) * 1000;

            Log::info('JOIN - Fallback quiz + questions', [
                'time_ms' => round($fallbackQueryTime, 2),
            ]);
        }

        if (!$activeQuiz) {
            return response()->json([
                'error' => 'No active quiz found for this room.'
            ], 404);
        }

        // ============================================================
        // 3. Build response
        // ============================================================

        $responseStart = microtime(true);

        $response = response()->json([
            'student_id' => $studentId,
            'quiz' => [
                'id' => $activeQuiz->id,
                'title' => $activeQuiz->title,
                'duration' => $activeQuiz->duration,
                'start_datetime' => $activeQuiz->start_datetime,
            ],
            'questions' => $activeQuiz->questions
        ]);

        $responseTime = (microtime(true) - $responseStart) * 1000;

        // ============================================================
        // TOTAL TIME
        // ============================================================

        $totalTime = (microtime(true) - $totalStart) * 1000;

        Log::info('JOIN - TOTAL PERFORMANCE', [
            'validation_ms' => round(
                ($validationTime - $totalStart) * 1000,
                2
            ),

            'teacher_query_ms' => round(
                $teacherQueryTime,
                2
            ),

            'active_quiz_query_ms' => round(
                $activeQuizQueryTime,
                2
            ),

            'fallback_query_ms' => round(
                $fallbackQueryTime,
                2
            ),

            'response_build_ms' => round(
                $responseTime,
                2
            ),

            'total_ms' => round(
                $totalTime,
                2
            ),

            'quiz_id' => $activeQuiz->id,

            'question_count' => $activeQuiz->questions->count(),
        ]);

        return $response;
    }


    /**
     * Submit quiz answers
     */
    public function submit(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'student_id' => 'required|string',
            'answers' => 'required|array'
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
