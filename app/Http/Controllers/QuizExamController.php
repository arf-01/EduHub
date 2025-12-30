<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;



class QuizExamController extends Controller
{
    //  Show the quiz to the student
    public function takeQuiz(Request $request, $quiz_id)
    {
        $studentId = session('student_id');
       

        if (!$studentId) {
            return redirect()->route('quiz.listStud')->with('error', 'Please enter the quiz room first.');
        }

        $quiz = Quiz::with('questions')->findOrFail($quiz_id);
        $startDatetime = Carbon::parse($quiz->start_datetime);
        $duration = $quiz->duration;
        $finishDateTime = $startDatetime->copy()->addSeconds($duration);
        $current = Carbon::now('Asia/Dhaka');

        if ($current->lte($startDatetime)) {
            return back()->with('error', 'The quiz has not started yet.');
        }

        if ($current->gte($finishDateTime)) {
            return back()->with('error', 'The quiz has already ended.');
        }

        //  Prepare questions array for frontend
     $questions = $quiz->questions->map(function ($q) {
    return [
        'id' =>$q->id,
        'text' => $q->text,
        'image' => $q->image
            ? asset('storage/' . ltrim($q->image, '/'))
            : null,

        'option1' => $q->option1,
        'option2' => $q->option2,
        'option3' => $q->option3,
        'option4' => $q->option4,
        'duration' => (int) $q->duration,
        // 'right_option' removed here — do not send it
    ];
});




        return view('student.questions', [
            'quiz' => $quiz,
            'duration' => $current->diffInSeconds($finishDateTime, false),
            'student_id' => $studentId,
            'questions' => $questions,
        ]);
    }

    // Set start time to now and calculate total quiz time from question durations
    public function startNow(Request $request, $id)
    {
       $current = (Carbon::now('Asia/Dhaka'));

        $quiz = Quiz::findOrFail($id);
        $quiz_id=$id;
        $totalDuration = $quiz->questions->sum('duration') ;
        
        $quiz->start_datetime = $current;
        $quiz->duration = $totalDuration;
        $quiz->save();

       // event(new QuizStatusUpdated($quiz_id, Auth::user()->room_name, $current,$totalDuration));
        
        return redirect()->route('quiz.list');
    }

   

}