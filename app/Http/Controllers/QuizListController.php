<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;
use App\Models\User;
use Carbon\Carbon;

class QuizListController extends Controller
{

public function showQuizList()
{
    if (Auth::check()) {
        
        $quizzes = Quiz::where('userid', Auth::id())->get();

        
        return view('teacher.allquizes', compact('quizzes'));
    }

    
    return redirect('/login')->with('error', 'Please log in to view your quizzes.');
}
public function showQuizDetails($id)
{
    
    $quiz = Quiz::with('questions')->findOrFail($id);
    $quiz->start_datetime = Carbon::parse($quiz->start_datetime)->setTimezone(config('app.timezone'));

   
    if ($quiz->userid !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    
    return view('teacher/EditQuiz', compact('quiz'));
}
public function enterRoom(Request $request)
{
   
    $validated = $request->validate([
        'student_id' => 'required|string',
        'room_name' => 'required|string',
    ]);

  
    session([
        'student_id' => $validated['student_id'],
        'room_name' => $validated['room_name'],
    ]);

   
    return redirect()->route('quiz.listStud');
}






public function showQuizListToStudents(Request $request)
{
    
    $studentId = session('student_id');
    $roomName = session('room_name');

    
    if (!$studentId || !$roomName) {
        return redirect()->back()->withErrors(['message' => 'Student ID or Room Name not found.']);
    }

   
    $teacher = User::where('room_name', $roomName)->first();

    if (!$teacher) {
        return redirect()->back()->withErrors(['room_name' => 'Room not found. Please try again.']);
    }

   
    $quizzes = Quiz::where('userid', $teacher->id)->get();

    
    foreach ($quizzes as $quiz) {
        $quiz->start_datetime = Carbon::parse($quiz->start_datetime);
    }

   
    return view('student.allquizes', [
        'quizzes' => $quizzes,
        'teacher' => $teacher,
        'student_id' => $studentId,  
    ]);
}





public function destroy($id)
{
   $quiz = Quiz::findOrFail($id);

   
    if (auth()->id() !== $quiz->userid) {
    return response()->json(['error' => 'Unauthorized'], 403);
}

    $quiz->delete();

    return response()->json(['message' => 'Quiz deleted successfully']);
}


    
}


