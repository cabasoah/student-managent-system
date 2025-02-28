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
        $quiz = Quiz::findOrFail($quizId);
       
        $studentResults = StudentQuizAttempt::where('quiz_id', $quizId)
        ->join('users', 'student_quiz_attempts.student_id', '=', 'users.id')
        ->select(
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as student_name"),
            'student_quiz_attempts.score',
            'student_quiz_attempts.created_at'
        )
        ->orderBy('student_quiz_attempts.created_at', 'desc')
        ->get();
          
        return view('quizzes.results', compact('quiz', 'studentResults'));
    }

    
}
