<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Result;
use App\Models\Question;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    /**
     * Display the quiz leaderboard.
     */
    public function showboard($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);

        if (Auth::check() && $quiz->userid !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $results = Result::with('details.question')
            ->where('quiz_id', $id)
            ->orderBy('score', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        $totalQuestions = $quiz->questions->count();
        $totalParticipants = $results->count();
        $highestScore = $results->max('score') ?? 0;
        $averageScore = $totalParticipants > 0 ? round($results->avg('score'), 1) : 0;
        $passRate = ($totalParticipants > 0 && $totalQuestions > 0)
            ? round(($results->filter(fn($r) => ($r->score / $totalQuestions) >= 0.5)->count() / $totalParticipants) * 100, 1)
            : 0;

        return view('teacher.leaderboard', compact(
            'quiz',
            'results',
            'totalQuestions',
            'totalParticipants',
            'highestScore',
            'averageScore',
            'passRate'
        ));
    }

    /**
     * Display score percentage distribution & performance analytics.
     */
    public function performanceGraph($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);

        if (Auth::check() && $quiz->userid !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $results = Result::where('quiz_id', $id)->get();
        $totalQuestions = $quiz->questions->count();

        $buckets = [
            '100%' => 0,
            '>80%' => 0,
            '>60%' => 0,
            '>40%' => 0,
            '<=40%' => 0,
        ];

        if ($totalQuestions > 0 && $results->count() > 0) {
            foreach ($results as $result) {
                $percentage = ($result->score / $totalQuestions) * 100;

                if ($percentage >= 100) {
                    $buckets['100%']++;
                } elseif ($percentage > 80) {
                    $buckets['>80%']++;
                } elseif ($percentage > 60) {
                    $buckets['>60%']++;
                } elseif ($percentage > 40) {
                    $buckets['>40%']++;
                } else {
                    $buckets['<=40%']++;
                }
            }
        }

        $totalParticipants = $results->count();
        $averageScore = $totalParticipants > 0 ? round($results->avg('score'), 1) : 0;
        $averagePercentage = ($totalParticipants > 0 && $totalQuestions > 0)
            ? round(($averageScore / $totalQuestions) * 100, 1)
            : 0;
        $highestScore = $results->max('score') ?? 0;
        $lowestScore = $totalParticipants > 0 ? $results->min('score') : 0;
        $passRate = ($totalParticipants > 0 && $totalQuestions > 0)
            ? round(($results->filter(fn($r) => ($r->score / $totalQuestions) >= 0.5)->count() / $totalParticipants) * 100, 1)
            : 0;

        return view('teacher.quiz_performance', compact(
            'quiz',
            'buckets',
            'totalQuestions',
            'totalParticipants',
            'averageScore',
            'averagePercentage',
            'highestScore',
            'lowestScore',
            'passRate'
        ));
    }

    /**
     * Export leaderboard results as PDF.
     */
    public function export($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);

        if (Auth::check() && $quiz->userid !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $results = Result::where('quiz_id', $id)
            ->orderBy('score', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        $rank = 1;
        foreach ($results as $result) {
            $result->rank = $rank++;
        }

        $totalQuestions = $quiz->questions->count();
        $pdf = Pdf::loadView('teacher.resultpdf', compact('quiz', 'results', 'totalQuestions'));
        return $pdf->download("quiz-{$quiz->id}-leaderboard.pdf");
    }

    /**
     * Clear all submissions for a quiz.
     */
    public function destroy($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if (Auth::check() && $quiz->userid !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        Result::where('quiz_id', $quizId)->delete();

        return redirect()->route('quiz.leaderboard', $quizId)->with('success', 'Leaderboard cleared successfully.');
    }
}
