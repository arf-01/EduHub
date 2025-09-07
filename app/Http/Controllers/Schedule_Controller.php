<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use Carbon\Carbon;
use App\Events\QuizStatusUpdated;
use Illuminate\Support\Facades\Auth;


class Schedule_Controller extends Controller {
    public function schedule(Request $request, $quiz_id)
{
    
    $request->validate([
       'start_datetime' => 'nullable|date',
      

    ]);

    
    $quiz = Quiz::findOrFail($quiz_id);

    
    $startDatetime = Carbon::createFromFormat('Y-m-d H:i', $request->start_datetime, config('app.timezone'));

    
    $quiz->start_datetime = $startDatetime;
    $totalDuration = $quiz->questions->sum('duration');
    $quiz->duration=$totalDuration;

     // event(new QuizStatusUpdated($quiz_id, Auth::user()->room_name, $startDatetime, $totalDuration));

    
    $quiz->save();

    
    return redirect()->route('quiz.list');
       
  }

}

