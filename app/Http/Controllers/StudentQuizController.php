<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\StudentQuizAttempt;
use App\Models\StudentAnswer;
use App\Repositories\QuizRepository;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

class StudentQuizController extends Controller
{
    public function index(Request $request)
    {

      $course_id = $request->query('course_id', 0);
      $current_school_session_id = $this->getSchoolCurrentSession();

      // Use a repository to get the quizzes
      $quizRepository = new QuizRepository();
      $quizzes = $quizRepository->getQuizzesForStudent($current_school_session_id, $course_id);

      // Pass data to the view
      return view('quizzes.students.list', [
          'quizzes' => $quizzes,
      ]);
    }
    public function attemptQuiz($quiz_id)
    {
        $quiz = Quiz::with('questions.options')->findOrFail($quiz_id);
        return view('quizzes.students.quiz', compact('quiz'));
    }

    public function saveAnswer(Request $request)
    {
        $request->validate([
            'attempt_id' => 'required|exists:student_quiz_attempts,id',
            'question_id' => 'required|exists:questions,id',
            'answer' => 'nullable|string',
            'option_id' => 'nullable|exists:options,id',
            'class_id' => 'nullable|integer|exists:classes,id',
            'section_id' => 'nullable|integer|exists:sections,id',
            'semester_id' => 'nullable|integer|exists:semesters,id',
            'session_id' => 'nullable|integer|exists:sessions,id',
        ]);
    
        $attempt = StudentQuizAttempt::findOrFail($request->attempt_id);
    
        // Ensure answer belongs to the correct quiz and student
        if ($attempt->student_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized attempt'], 403);
        }
    
        StudentAnswer::updateOrCreate(
            [
                'attempt_id' => $request->attempt_id,
                'question_id' => $request->question_id,
            ],
            [
                'option_id' => $request->option_id,
                'answer_text' => $request->answer,
                'class_id' => $request->class_id ?? $attempt->class_id,
                'section_id' => $request->section_id ?? $attempt->section_id,
                'semester_id' => $request->semester_id ?? $attempt->semester_id,
                'session_id' => $request->session_id ?? $attempt->session_id,
            ]
        );
    
        return response()->json(['message' => 'Answer saved successfully']);
    }

    public function submitQuiz(Request $request)
    {
        $request->validate([
            'attempt_id' => 'required|exists:student_quiz_attempts,id',
            'class_id' => 'nullable|integer|exists:classes,id',
            'section_id' => 'nullable|integer|exists:sections,id',
            'semester_id' => 'nullable|integer|exists:semesters,id',
            'session_id' => 'nullable|integer|exists:sessions,id',
        ]);

        $attempt = StudentQuizAttempt::findOrFail($request->attempt_id);

        // Ensure the quiz is associated with the correct student
        if ($attempt->student_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized submission'], 403);
        }

        // Calculate score
        $score = 0;
        foreach ($attempt->quiz->questions as $question) {
            $studentAnswer = $attempt->answers()->where('question_id', $question->id)->first();

            if ($studentAnswer) {
                if ($question->type == 'single_choice' || $question->type == 'multiple_choice') {
                    $correctOptions = $question->options()->where('is_correct', true)->pluck('id')->toArray();
                    $selectedOptions = [$studentAnswer->option_id];

                    if ($question->type == 'multiple_choice') {
                        $selectedOptions = $attempt->answers()->where('question_id', $question->id)->pluck('option_id')->toArray();
                    }

                    if ($selectedOptions == $correctOptions) {
                        $score += 1;
                    }
                }
            }
        }

        // Update attempt score and related fields
        $attempt->update([
            'score' => $score,
            'class_id' => $request->class_id ?? $attempt->class_id,
            'section_id' => $request->section_id ?? $attempt->section_id,
            'semester_id' => $request->semester_id ?? $attempt->semester_id,
            'session_id' => $request->session_id ?? $attempt->session_id,
        ]);

        return response()->json(['message' => 'Quiz submitted successfully', 'score' => $score]);
    }

    // Store a student's quiz attempt
    // public function attemptQuiz(Request $request) {
    //     $request->validate([
    //         'student_id' => 'required|exists:users,id',
    //         'quiz_id' => 'required|exists:quizzes,id',
    //         'answers' => 'required|array',
    //         'answers.*.question_id' => 'required|exists:questions,id',
    //         'answers.*.option_id' => 'nullable|exists:options,id',
    //         'answers.*.answer_text' => 'nullable|string'
    //     ]);

    //     $attempt = StudentQuizAttempt::create([
    //         'student_id' => $request->student_id,
    //         'quiz_id' => $request->quiz_id,
    //         'score' => null  
    //     ]);

       
    //     foreach ($request->answers as $answer) {
    //         StudentAnswer::create([
    //             'attempt_id' => $attempt->id,
    //             'question_id' => $answer['question_id'],
    //             'option_id' => $answer['option_id'] ?? null,
    //             'answer_text' => $answer['answer_text'] ?? null
    //         ]);
    //     }

    //     return response()->json(['message' => 'Quiz attempt submitted successfully'], 201);
    // }

    // Get quiz attempts by a student
    public function getStudentAttempts($student_id) {
        $attempts = StudentQuizAttempt::where('student_id', $student_id)->with('quiz')->get();
        return response()->json($attempts);
    }
}
