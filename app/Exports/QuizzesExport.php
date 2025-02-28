<?php

namespace App\Exports;

use App\Models\Quiz;
use App\Models\StudentQuizAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Interfaces\SchoolSessionInterface;
use App\Traits\SchoolSession;
use App\Repositories\QuizRepository;

class QuizzesExport implements FromView
{
    use SchoolSession;
    protected $schoolSessionRepository;

    /**
    * Create a new Controller instance
    * 
    * @param CourseInterface $schoolCourseRepository
    * @return void
    */
    public function __construct(SchoolSessionInterface $schoolSessionRepository) {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    public function view(): View
    {
        $userId = Auth::id();
        $current_school_session_id = $this->getSchoolCurrentSession();
        $course_id = request()->query('course_id', 0);

        // Get quizzes available to the student
        $quizRepository = new QuizRepository();
        $quizzes = $quizRepository->getQuizzesForStudent($current_school_session_id, $course_id);

        // Get attempted quizzes with scores
        $attemptedQuizzes = StudentQuizAttempt::where('student_id', $userId)
            ->select('quiz_id', 'score')
            ->get()
            ->keyBy('quiz_id'); 

        foreach ($quizzes as $quiz) {
            // Count single-choice and multiple-choice questions
            $choiceQuestionsCount = DB::table('questions')
                ->where('quiz_id', $quiz->id)
                ->whereIn('type', ['single_choice', 'multiple_choice'])
                ->count();

            // Sum max_mark for open-ended questions
            $openEndedMarks = DB::table('questions')
                ->where('quiz_id', $quiz->id)
                ->where('type', 'open_ended')
                ->sum('max_mark');

            // Total Marks = (Single Choice + Multiple Choice Questions * 1) + Open-Ended Questions Marks
            $quiz->total_marks = $choiceQuestionsCount + $openEndedMarks;

            // Student's score
            $quiz->student_score = $attemptedQuizzes[$quiz->id]->score ?? 'Not Attempted';
        }

        return view('quizzes.students.export_quizzes', compact('quizzes'));
    }
}
