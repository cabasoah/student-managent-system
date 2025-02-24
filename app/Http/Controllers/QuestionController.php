<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Option;

class QuestionController extends Controller
{
     // Store a new question
     public function store(Request $request) {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'question_text' => 'required|string',
            'type' => 'required|in:single_choice,multiple_choice,open_ended',
            'options' => 'nullable|array',
            'options.*.option_text' => 'nullable|string',
            'options.*.is_correct' => 'nullable|boolean'
        ]);

        $question = Question::create([
            'quiz_id' => $request->quiz_id,
            'question_text' => $request->question_text,
            'type' => $request->type
        ]);

        // If question type is single/multiple choice, save options
        if (in_array($request->type, ['single_choice', 'multiple_choice']) && $request->has('options')) {
            foreach ($request->options as $option) {
                $question->options()->create([
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'] ?? false
                ]);
            }
        }

        return response()->json(['message' => 'Question created successfully', 'question' => $question], 201);
    }

    // Get all questions for a quiz
    public function getQuestions($quiz_id) {
        $questions = Question::where('quiz_id', $quiz_id)->with('options')->get();
        return response()->json($questions);
    }
}
