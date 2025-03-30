<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Option;
use App\Models\Quiz;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuizQuestionsImport;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function index($quizId)
    {
        $quiz = Quiz::with('questions.options')->findOrFail($quizId);
        return view('quizzes.questions.index', compact('quiz'));
    }

    public function create($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        return view('quizzes.questions.form', compact('quiz'));
    }

    public function store(Request $request, $quizId)
    {
        $validatedData = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:single_choice,multiple_choice,open_ended',
            'options' => 'required_if:question_type,single_choice,multiple_choice|array',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'nullable|boolean',
            'correct_answer' => 'nullable|required_if:question_type,open_ended|string',
            'max_mark' => 'nullable|required_if:question_type,open_ended|integer|min:1',
        ]);
    
        $question = new Question();
        $question->quiz_id = $quizId;
        $question->teacher_id = auth()->id();
        $question->question_text = $validatedData['question_text'];
        $question->type = $validatedData['question_type'];
    
        if ($question->type == 'open_ended') {
            $question->correct_answer = $validatedData['correct_answer'];
            $question->max_mark = $validatedData['max_mark'];
        }
    
        $question->save();
    
        if (isset($validatedData['options']) && ($question->type !== 'open_ended')) {
            foreach ($validatedData['options'] as $option) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $option['text'],
                    'is_correct' => $option['is_correct'] ?? false,
                ]);
            }
        }
    
        return redirect()->route('admin.quizzes.questions', $quizId)->with('status', 'Question added successfully!');
    }
    

    public function edit($quizId, $questionId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $question = Question::with('options')->findOrFail($questionId);
        return view('quizzes.questions.form', compact('quiz', 'question'));
    }

    public function update(Request $request, $quizId, $questionId)
    {
        $question = Question::findOrFail($questionId);
    
        $validatedData = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:single_choice,multiple_choice,open_ended',
            'options' => 'required_if:question_type,single_choice,multiple_choice|array',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'nullable|boolean',
            'correct_answer' => 'nullable|string|required_if:question_type,open_ended',
            'max_mark' => 'nullable|integer|required_if:question_type,open_ended',
        ]);
    
        // Update question details
        $question->update([
            'question_text' => $validatedData['question_text'],
            'type' => $validatedData['question_type'],
        ]);
    
        // Handle Open-Ended Questions
        if ($validatedData['question_type'] === 'open_ended') {
            $question->update([
                'correct_answer' => $validatedData['correct_answer'] ?? null,
                'max_mark' => $validatedData['max_mark'] ?? 0,
            ]);
            $question->options()->delete(); // Ensure options are removed if previously set
        } else {
            // If the question type is NOT open-ended, handle options
            $question->options()->delete();
            if (isset($validatedData['options'])) {
                foreach ($validatedData['options'] as $option) {
                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $option['text'],
                        'is_correct' => $option['is_correct'] ?? false,
                    ]);
                }
            }
        }
    
        return redirect()->route('admin.quizzes.questions', $quizId)
                         ->with('status', 'Question updated successfully!');
    }
    

    public function destroy($quizId, $questionId)
    {
        $question = Question::findOrFail($questionId);
        $question->delete();
        
        return redirect()->route('admin.quizzes.questions', $quizId)
                         ->with('status', 'Question deleted successfully!');
    }

    public function showBulkUploadForm($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        return view('quizzes.questions.bulk_upload', compact('quiz'));
    }

    public function bulkUpload(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Excel::import(new QuizQuestionsImport($quizId, auth()->id()), $request->file('file'));

        return redirect()->route('admin.quizzes.questions', $quizId)
                         ->with('status', 'Questions uploaded successfully!');
    }
}
