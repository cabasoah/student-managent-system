<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\StudentAnswer;
use App\Models\StudentQuizAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
     // Display all quizzes
     public function index() {
        $quizzes = Quiz::with('course')->get();
        return view('quizzes.all', compact('quizzes'));
    }

    public function create() {
        $courses = Course::all();
        return view('quizzes.form', compact('courses'));
    }

    // Store a new quiz
    public function store(Request $request) {
        $teacher_id = Auth::user()->id;
        $course = Course::find($request->course_id);
        $section = Section::where(['class_id' => $course->class_id, 'session_id' => $course->session_id])->first();
        $request->merge(['section_id' => $section->id, 'semester_id' => $course->semester_id, 'class_id' => $course->class_id,'teacher_id' => $teacher_id, 'session_id' => $course->session_id]);
     
        Quiz::create($request->all());
        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz created successfully!');
    }

    public function edit(Quiz $quiz) {
        $courses = Course::all();
        return view('quizzes.form', compact('quiz', 'courses'));
    }

    public function update(Request $request, Quiz $quiz) {
        $quiz->update($request->all());
        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz updated successfully!');
    }

    public function destroy(Quiz $quiz) {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz deleted successfully!');
    }

    public function toggleVisibility(Quiz $quiz)
    {
        $quiz->is_visible_to_student = !$quiz->is_visible_to_student;
        $quiz->save();

        return response()->json([
            'success' => true,
            'is_visible' => $quiz->is_visible_to_student
        ]);
    }

    public function showResults($quizId)
    {
        $quiz = Quiz::with(['questions.options'])->findOrFail($quizId);

        // Get all attempts with answers and options
        $studentAttempts = StudentQuizAttempt::with(['student', 'answers.option'])
            ->where('quiz_id', $quizId)
            ->get();

        $studentResults = [];

        foreach ($studentAttempts as $attempt) {
            $totalMarks = 0;
            $earnedMarks = 0;

            foreach ($quiz->questions as $question) {
                $studentAnswers = $attempt->answers->where('question_id', $question->id);

                if ($question->type === 'multiple_choice') {
                    $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
                    $studentSelectedOptions = $studentAnswers->pluck('option_id')->toArray();

                    if (
                        count(array_diff($studentSelectedOptions, $correctOptions)) === 0 &&
                        count(array_diff($correctOptions, $studentSelectedOptions)) === 0
                    ) {
                        $earnedMarks += 1;
                    }

                    $totalMarks += 1;
                } elseif ($question->type === 'single_choice') {
                    $answer = $studentAnswers->first();
                    $correctOptionIds = $question->options->where('is_correct', true)->pluck('id');

                    if ($answer && $answer->option_id && $correctOptionIds->contains($answer->option_id)) {
                        $earnedMarks += 1;
                    }

                    $totalMarks += 1;
                } elseif ($question->type === 'open_ended') {
                    $maxMark = $question->max_mark ?? 1;
                    $awardedMark = $studentAnswers->first()->marks_awarded ?? 0;

                    $earnedMarks += $awardedMark;
                    $totalMarks += $maxMark;
                }
            }

            $score = ($totalMarks > 0) ? round(($earnedMarks / $totalMarks) * 100, 2) : 0;

            $studentResults[] = [
                'student_name' => $attempt->student->first_name . ' ' . $attempt->student->last_name,
                'score' => $score,
                'earned_marks' => $earnedMarks,
                'total_marks' => $totalMarks,
                'attempted_at' => $attempt->created_at,
            ];
        }
        // dd($studentResults[0]);
        return view('quizzes.results', compact('quiz', 'studentResults'));
    }


    public function resetAllAttempts($quiz_id)
    {
        $attempts = StudentQuizAttempt::where('quiz_id', $quiz_id)->get();

        foreach ($attempts as $attempt) {
            // Delete related student answers
            $attempt->answers()->delete(); // assuming hasMany relation
            $attempt->delete();
        }

        return back()->with('success', 'All quiz attempts for this quiz have been reset.');
    }

    public function updateMarks(Request $request)
    {
        $request->validate([
            'answer_id' => 'required|exists:student_answers,id',
            'marks_awarded' => 'required|numeric|min:0',
        ]);

        $answer = StudentAnswer::findOrFail($request->answer_id);
        
        $question = $answer->question;
        $maxMark = $question->max_mark ?? 1;

        if ($request->marks_awarded > $maxMark) {
            return back()->with('status', 'Awarded marks cannot exceed maximum marks.');
        }

        $answer->marks_awarded = $request->marks_awarded;
        $answer->save();

        return back()->with('status', 'Marks updated successfully.');
    }


    
}
