<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\ResultDetail;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ResultController extends Controller
{
   public function storeResult(Request $request)
{
    $validated = $request->validate([
        'answers' => 'required|array',
        'answers.*.question_id' => 'required|integer|exists:questions,id',
        'answers.*.selected_option' => 'nullable|integer',
    ]);

    $studentId = Session::get('student_id');
    if (!$studentId) {
        return back()->with('error', 'Student session expired or not found.');
    }

    $score = 0;
    $answersToSave = [];
    
    // Get all question_ids from submitted answers
    $questionIds = collect($validated['answers'])->pluck('question_id');
    
    // Fetch all related questions in one query
    $questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');
    
    // Loop through answers
    foreach ($validated['answers'] as $answer) {
        $question = $questions->get($answer['question_id']);
        if (!$question) continue; // skip if question not found
    
        $selected = (int) $answer['selected_option'];
        $rightOption = (int) $question->right_option;
    
        if ($selected === 0) {
            // skipped, no score change
        } elseif ($rightOption === $selected) {
            $score += 1;
        } else {
            $score -= 0.25;
        }
    
        $answersToSave[] = [
            'question_id' => $question->id,
            'selected_option' => $selected,
        ];
    }
    
    $quiz_id = $questions->first()->quiz_id ?? null;
    
    $score = max(0, $score);

    $alreadySubmitted = Result::where('student_id', $studentId)
    ->where('quiz_id', $quiz_id)
    ->exists();

    if ($alreadySubmitted) {
        return back();
    }
    



    $result = Result::create([
        'student_id' => $studentId,
        'quiz_id' => $quiz_id,
        'score' => $score,
    ]);

    foreach ($answersToSave as &$data) {
        $data['result_id'] = $result->id;
    }
    ResultDetail::insert($answersToSave);
    

    
    $details = ResultDetail::where('result_id', $result->id)
    ->with('question')
    ->get();

$total     = $details->count();
$attempted = $details->where('selected_option', '!=', 0)->count();
$skipped   = $total - $attempted; // faster than filtering again
$score     = $score; // already calculated

return view('student.detailedanalysis', compact(
    'details',
    'total',
    'attempted',
    'skipped',
    'score'
));

}



}
