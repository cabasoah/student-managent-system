<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\StudentAnswer;
use App\Models\StudentQuizAttempt;
use Illuminate\Support\Facades\Auth;

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
    
}
