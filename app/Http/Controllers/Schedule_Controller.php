<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Schedule_Controller extends Controller
{
    public function schedule(Request $request, $quiz_id)
    {
        $request->validate([
            'start_datetime' => 'nullable|string',
        ]);

        $quiz = Quiz::findOrFail($quiz_id);

        if ($request->filled('start_datetime')) {
            try {
                $startDatetime = Carbon::parse($request->start_datetime, config('app.timezone'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Invalid date/time format provided.');
            }

            $quiz->start_datetime = $startDatetime;
            $totalDuration = $quiz->questions->sum('duration');
            if ($totalDuration <= 0) {
                $totalDuration = max(60, $quiz->questions->count() * 60);
            }
            $quiz->duration = $totalDuration;
            $quiz->save();

            return redirect()->back()->with('success', 'Quiz scheduled for '.$startDatetime->format('d M, Y H:i'));
        } else {
            $quiz->start_datetime = null;
            $quiz->save();

            return redirect()->back()->with('success', 'Quiz schedule cleared.');
        }
    }
}
