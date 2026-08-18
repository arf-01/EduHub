<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Quiz;
use Carbon\Carbon;

class QuestionControlller extends Controller
{
    /**
     * Store a new quiz.
     */
    public function storeQuiz(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You need to be logged in to create a quiz.');
        }

        $request->validate([
            'quiz_title' => 'required|string|max:255',
        ]);

        $quiz = new Quiz();
        $quiz->title = $request->quiz_title;
        $quiz->userid = Auth::id();
        $quiz->start_datetime = Carbon::now();
        $quiz->duration = -1;
        $quiz->save();

        return redirect()->route('quiz.details', $quiz->id)->with('success', 'Quiz created successfully! Start adding questions below.');
    }

    /**
     * Show the edit view for an existing question.
     */
    public function edittoupdate($id)
    {
        $question = Question::with('quiz')->findOrFail($id);

        if ($question->quiz->userid !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $quiz = $question->quiz;
        return view('teacher.question_update', compact('question', 'quiz'));
    }

    /**
     * Update an existing question.
     */
    public function update(Request $request, $id)
    {
        $question = Question::with('quiz')->findOrFail($id);

        if ($question->quiz->userid !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'question_text' => 'nullable|string|max:65535',
            'text' => 'nullable|string|max:65535',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'remove_image' => 'nullable|boolean',
            'options' => 'nullable|array|min:4|max:4',
            'options.*' => 'nullable|string|max:1000',
            'option1' => 'nullable|string|max:1000',
            'option2' => 'nullable|string|max:1000',
            'option3' => 'nullable|string|max:1000',
            'option4' => 'nullable|string|max:1000',
            'correct_option' => 'nullable|integer|in:1,2,3,4',
            'right_option' => 'nullable|integer|in:1,2,3,4',
            'duration' => 'required|numeric|min:1',
        ]);

        $text = $request->filled('question_text') ? $request->question_text : $request->input('text');
        
        $opt1 = $request->input('options.1') ?? $request->input('option1');
        $opt2 = $request->input('options.2') ?? $request->input('option2');
        $opt3 = $request->input('options.3') ?? $request->input('option3');
        $opt4 = $request->input('options.4') ?? $request->input('option4');

        if (!$opt1 || !$opt2 || !$opt3 || !$opt4) {
            return back()->withErrors(['options' => 'All 4 options are required.'])->withInput();
        }

        $rightOption = $request->input('correct_option') ?? $request->input('right_option');
        if (!$rightOption) {
            return back()->withErrors(['correct_option' => 'Please select the correct option.'])->withInput();
        }

        $questionData = [
            'text' => $text,
            'option1' => $opt1,
            'option2' => $opt2,
            'option3' => $opt3,
            'option4' => $opt4,
            'right_option' => $rightOption,
            'duration' => $request->duration,
        ];

        if ($request->hasFile('question_image')) {
            if ($question->image && Storage::disk('public')->exists($question->image)) {
                Storage::disk('public')->delete($question->image);
            }
            $path = $request->file('question_image')->store('questions', 'public');
            $questionData['image'] = $path;
        } elseif ($request->boolean('remove_image')) {
            if ($question->image && Storage::disk('public')->exists($question->image)) {
                Storage::disk('public')->delete($question->image);
            }
            $questionData['image'] = null;
        }

        $question->update($questionData);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Question updated successfully.',
                'question' => $question
            ]);
        }

        return redirect()->route('quiz.details', $question->quiz_id)->with('success', 'Question updated successfully!');
    }

    /**
     * Add a new question to the quiz.
     */
    public function add(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'question_text' => 'nullable|string|max:65535',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'options' => 'required|array|min:4|max:4',
            'options.*' => 'required|string|max:1000',
            'correct_option' => 'required|integer|in:1,2,3,4',
            'duration' => 'required|integer|min:1',
        ]);

        $quiz = Quiz::findOrFail($request->quiz_id);

        if ($quiz->userid !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if (!$request->filled('question_text') && !$request->hasFile('question_image')) {
            return response()->json([
                'message' => 'Please provide question text or attach an image.',
                'errors' => ['question_text' => ['Either question text or an image is required.']]
            ], 422);
        }

        $questionData = [
            'option1' => $request->options[1],
            'option2' => $request->options[2],
            'option3' => $request->options[3],
            'option4' => $request->options[4],
            'right_option' => $request->correct_option,
            'duration' => $request->duration,
            'text' => $request->question_text,
        ];

        if ($request->hasFile('question_image')) {
            $path = $request->file('question_image')->store('questions', 'public');
            $questionData['image'] = $path;
        }

        $question = $quiz->questions()->create($questionData);

        // Include full image URL for immediate client-side rendering
        $question->image_url = $question->image ? asset('storage/' . $question->image) : null;

        return response()->json($question);
    }

    /**
     * Delete a question.
     */
    public function destroyQuestion($id)
    {
        $question = Question::with('quiz')->find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found.'], 404);
        }

        if ($question->quiz && $question->quiz->userid !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if ($question->image && Storage::disk('public')->exists($question->image)) {
            Storage::disk('public')->delete($question->image);
        }

        $question->delete();

        return response()->json(['message' => 'Question deleted successfully.']);
    }
}