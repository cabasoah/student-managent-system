<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\StudentQuizAttempt as QuizAttempt;

class QuizController extends Controller
{
     // Display all quizzes
     public function index() {
        return response()->json(Quiz::with('questions')->get());
    }

    // Store a new quiz
    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id'
        ]);

        $quiz = Quiz::create($request->all());

        return response()->json(['message' => 'Quiz created successfully', 'quiz' => $quiz], 201);
    }

    // Show a specific quiz
    public function show($id) {
        $quiz = Quiz::with('questions.options')->findOrFail($id);
        return response()->json($quiz);
    }

    public function saveAnswer(Request $request)
{
    $request->validate([
        'quiz_id' => 'required|exists:quizzes,id',
        'question_id' => 'required|exists:questions,id',
        'answer' => 'required|exists:options,id'
    ]);

    QuizAttempt::updateOrCreate(
        [
            'user_id' => auth()->id(),
            'quiz_id' => $request->quiz_id,
            'question_id' => $request->question_id
        ],
        ['selected_option' => $request->answer]
    );

    return response()->json(['message' => 'Answer saved successfully!']);
}

}
