<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\StudentQuizAttempt;
use App\Models\StudentAnswer;
use App\Repositories\QuizRepository;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\SchoolSessionInterface;
use App\Traits\SchoolSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
class StudentQuizController extends Controller
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
    
    public function index(Request $request)
    {
        $userId = Auth::id();
        $course_id = $request->query('course_id', 0);
        $current_school_session_id = $this->getSchoolCurrentSession();
    
        $quizRepository = new QuizRepository();
        $quizzes = $quizRepository->getQuizzesForStudent($current_school_session_id, $course_id);
    
        $attemptedQuizzes = StudentQuizAttempt::where('student_id', $userId)
            ->select('quiz_id', 'score')
            ->get()
            ->keyBy('quiz_id'); 
        
        foreach ($quizzes as $quiz) {
            $choiceQuestionsCount = DB::table('questions')
                ->where('quiz_id', $quiz->id)
                ->whereIn('type', ['single_choice', 'multiple_choice'])
                ->count();

            $openEndedMarks = DB::table('questions')
                ->where('quiz_id', $quiz->id)
                ->where('type', 'open_ended')
                ->sum('max_mark');

            // Total Marks = (Single Choice + Multiple Choice Questions * 1) + Open-Ended Questions Marks
            $quiz->total_marks = $choiceQuestionsCount + $openEndedMarks;
        }
    
        return view('quizzes.students.list', [
            'quizzes' => $quizzes,
            'attemptedQuizzes' => $attemptedQuizzes
        ]);
    }
    

    public function attemptQuiz($quiz_id)
    {
        $student_id = Auth::user()->id;
        $quiz = Quiz::with('questions.options')->findOrFail($quiz_id);
        // dd($quiz);
        $attempt = StudentQuizAttempt::firstOrCreate(
            [
                'student_id' => $student_id,
                'quiz_id' => $quiz_id,
            ],
            [
                'class_id' => $quiz->class_id, 
                'section_id' => $quiz->section_id,
                'semester_id' => $quiz->semester_id,  
                'session_id' => $quiz->session_id,    
                'score' => 0,
            ]
        );
        // dd($quiz);
        return view('quizzes.students.quiz', compact('quiz', 'attempt'));
    }

    public function saveAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attempt_id' => 'required|exists:student_quiz_attempts,id',
            'question_id' => 'required|exists:questions,id',
            'answer' => 'nullable|string',
            'option_id' => 'nullable|exists:options,id',
            'class_id' => 'nullable|integer|exists:school_classes,id',
            'section_id' => 'nullable|integer|exists:sections,id',
            'semester_id' => 'nullable|integer|exists:semesters,id',
            'session_id' => 'nullable|integer|exists:school_sessions,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 422);
        }
    
        $attempt = StudentQuizAttempt::findOrFail($request->attempt_id);
    
        if ($attempt->student_id !== auth()->id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized attempt'], 403);
        }
    
        $question = Question::findOrFail($request->question_id);
    
        StudentAnswer::updateOrCreate(
            [
                'attempt_id' => $request->attempt_id,
                'question_id' => $request->question_id,
            ],
            [
                'option_id' => $question->type === 'open_ended' ? null : $request->option_id,
                'answer_text' =>  $request->answer,
                'class_id' => $request->class_id ?? $attempt->class_id,
                'section_id' => $request->section_id ?? $attempt->section_id,
                'semester_id' => $request->semester_id ?? $attempt->semester_id,
                'session_id' => $request->session_id ?? $attempt->session_id,
            ]
        );
    
        return response()->json(['success' => true, 'message' => 'Answer saved successfully']);
    }
    public function submitQuiz(Request $request)
    {
        $request->validate([
            'attempt_id' => 'required|exists:student_quiz_attempts,id',
            'class_id' => 'nullable|integer|exists:school_classes,id',
            'section_id' => 'nullable|integer|exists:sections,id',
            'semester_id' => 'nullable|integer|exists:semesters,id',
            'session_id' => 'nullable|integer|exists:school_sessions,id',
        ]);
    
        // Fetch the attempt with necessary relationships
        $attempt = StudentQuizAttempt::findOrFail($request->attempt_id);
        
        // Fetch the quiz and its questions efficiently
        $quiz = $attempt->quiz()->with([
            'questions' => function ($query) {
                $query->select('id', 'quiz_id', 'type', 'correct_answer', 'max_mark');
            },
            'questions.options' => function ($query) {
                $query->select('id', 'question_id', 'is_correct');
            }
        ])->first();
    
        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }
    
        // Ensure the authenticated user is the owner of the attempt
        if ($attempt->student_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized submission'], 403);
        }
    
        $score = 0;
    
        // Fetch all student answers in bulk to avoid repeated queries
        $studentAnswers = $attempt->answers()->pluck('option_id', 'question_id');
    
        foreach ($quiz->questions as $question) {
            $studentAnswer = $studentAnswers[$question->id] ?? null;
    
            if ($studentAnswer) {
                if ($question->type == 'single_choice' || $question->type == 'multiple_choice') {
                    // Fetch correct options for this question
                    $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
    
                    // If it's multiple-choice, get all selected options
                    $selectedOptions = is_array($studentAnswer) ? $studentAnswer : [$studentAnswer];
    
                    if ($question->type == 'multiple_choice') {
                        $selectedOptions = $attempt->answers()
                            ->where('question_id', $question->id)
                            ->pluck('option_id')
                            ->toArray();
                    }
    
                    // Compare selected options with correct ones
                    if ($selectedOptions == $correctOptions) {
                        $score += 1;
                    }
                } elseif ($question->type == 'open_ended' && !empty($question->correct_answer)) {
                    // Calculate similarity score for open-ended questions
                    $studentText = strtolower(trim($studentAnswer));
                    $correctText = strtolower(trim($question->correct_answer));
    
                    $levenshteinDistance = levenshtein($studentText, $correctText);
                    $maxLength = max(strlen($studentText), strlen($correctText));
    
                    $similarityPercentage = ($maxLength > 0) ? (1 - ($levenshteinDistance / $maxLength)) * 100 : 0;
    
                    if ($similarityPercentage < 90) {
                        $synonymMatch = $this->checkSynonyms($studentText, $correctText);
                        if ($synonymMatch) {
                            $similarityPercentage += 15;
                        }
                    }
    
                    // Assign marks based on similarity threshold
                    if ($similarityPercentage >= 90) {
                        $score += $question->max_mark;
                    } elseif ($similarityPercentage >= 70) {
                        $score += $question->max_mark * 0.75;
                    } elseif ($similarityPercentage >= 50) {
                        $score += $question->max_mark * 0.5;
                    }
                }
            }
        }
    
        // Update the attempt with the computed score and additional fields
        $attempt->update([
            'score' => $score,
            'class_id' => $request->class_id ?? $attempt->class_id,
            'section_id' => $request->section_id ?? $attempt->section_id,
            'semester_id' => $request->semester_id ?? $attempt->semester_id,
            'session_id' => $request->session_id ?? $attempt->session_id,
        ]);
    
        return response()->json([
            'message' => 'Quiz submitted successfully',
            'score' => $score,
            'questions' => $quiz->questions
        ]);
    }
    
    // public function submitQuiz(Request $request)
    // {
    //     $request->validate([
    //         'attempt_id' => 'required|exists:student_quiz_attempts,id',
    //         'class_id' => 'nullable|integer|exists:school_classes,id',
    //         'section_id' => 'nullable|integer|exists:sections,id',
    //         'semester_id' => 'nullable|integer|exists:semesters,id',
    //         'session_id' => 'nullable|integer|exists:school_sessions,id',
    //     ]);

    //     $studentattempt = StudentQuizAttempt::findOrFail($request->attempt_id);
    //     $attempt = $studentattempt->quiz()->with('questions.options')->first();

    
    //     $attempt = StudentQuizAttempt::with('quiz.questions.options')->findOrFail($request->attempt_id);

    //     if ($attempt->student_id !== auth()->id()) {
    //         return response()->json(['message' => 'Unauthorized submission'], 403);
    //     }
    
    //     $score = 0;
    
    //     foreach ($attempt->quiz->questions as $question) {
    //         $studentAnswer = $attempt->answers()->where('question_id', $question->id)->first();
    
    //         if ($studentAnswer) {
    //             if ($question->type == 'single_choice' || $question->type == 'multiple_choice') {
    //                 $correctOptions = $question->options()->where('is_correct', true)->pluck('id')->toArray();
    //                 $selectedOptions = [$studentAnswer->option_id];
    
    //                 if ($question->type == 'multiple_choice') {
    //                     $selectedOptions = $attempt->answers()->where('question_id', $question->id)->pluck('option_id')->toArray();
    //                 }
    
    //                 if ($selectedOptions == $correctOptions) {
    //                     $score += 1;
    //                 }
    //             } elseif ($question->type == 'open_ended' && !empty($question->correct_answer)) {
    //                 // Calculate similarity
    //                 $studentText = strtolower(trim($studentAnswer->answer_text));
    //                 $correctText = strtolower(trim($question->correct_answer));
    
    //                 $levenshteinDistance = levenshtein($studentText, $correctText);
    //                 $maxLength = max(strlen($studentText), strlen($correctText));
    
    //                 // Avoid division by zero
    //                 $similarityPercentage = ($maxLength > 0) ? (1 - ($levenshteinDistance / $maxLength)) * 100 : 0;
    
    //                 // Check for synonyms if similarity is low
    //                 if ($similarityPercentage < 90) {
    //                     $synonymMatch = $this->checkSynonyms($studentText, $correctText);
    //                     if ($synonymMatch) {
    //                         $similarityPercentage += 15; // Boost similarity by 15% if synonyms match
    //                     }
    //                 }
    
    //                 // Assign partial marks based on similarity threshold
    //                 if ($similarityPercentage >= 90) {
    //                     $score += $question->max_mark;
    //                 } elseif ($similarityPercentage >= 70) {
    //                     $score += $question->max_mark * 0.75;
    //                 } elseif ($similarityPercentage >= 50) {
    //                     $score += $question->max_mark * 0.5;
    //                 }
    //             }
    //         }
    //     }
    
    //     // Update attempt score
    //     $attempt->update([
    //         'score' => $score,
    //         'class_id' => $request->class_id ?? $attempt->class_id,
    //         'section_id' => $request->section_id ?? $attempt->section_id,
    //         'semester_id' => $request->semester_id ?? $attempt->semester_id,
    //         'session_id' => $request->session_id ?? $attempt->session_id,
    //     ]);
    
    //     return response()->json(['message' => 'Quiz submitted successfully', 'score' => $score,'questions' => $attempt->quiz->questions]);
    // }
    
    /**
     * Check if two sentences have synonymous words.
     */
    private function checkSynonyms($studentAnswer, $correctAnswer)
    {
        $wordsStudent = explode(' ', $studentAnswer);
        $wordsCorrect = explode(' ', $correctAnswer);
    
        foreach ($wordsStudent as $word) {
            foreach ($wordsCorrect as $correctWord) {
                if ($this->areSynonyms($word, $correctWord)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Check if two words are synonyms using WordNet API.
     */
    private function areSynonyms($word1, $word2)
    {
        $response = Http::get("https://api.datamuse.com/words?rel_syn=$word1");
        $synonyms = collect($response->json())->pluck('word')->toArray();
    
        return in_array($word2, $synonyms);
    }

    public function quizResults($attempt_id)
    {
        $attempt = StudentQuizAttempt::with(['quiz.questions.options', 'answers.option'])->find($attempt_id);
    
        if (!$attempt) {
            return redirect()->route('home')->with('error', 'Quiz attempt not found.');
        }
    
        $totalMarks = 0;
        $earnedMarks = 0;
        $correctAnswers = 0;
    
        $questionsWithAnswers = [];
    
        $studentAnswer = $attempt->answers->where('question_id', $attempt->quiz->questions[0]->id)->first();
      
        foreach ($attempt->quiz->questions as $question) {
            $studentAnswer = $attempt->answers->where('question_id', $question->id)->first();
           
            $studentResponseText = null;
            $correctResponseText = null;
            $isCorrect = false;
    
            if ($question->type === 'single_choice' || $question->type === 'multiple_choice') {
                $correctOption = $question->options->where('is_correct', true)->first();
    
                if ($studentAnswer && $studentAnswer->option_id) {
                    $studentResponseText = $studentAnswer->answer_text;
                }
    
                if ($correctOption) {
                    $correctResponseText = $correctOption->option_text;
                }
    
                if ($studentAnswer && $correctOption && $studentAnswer->option_id == $correctOption->id) {
                    $correctAnswers++;
                    $earnedMarks += 1;
                    $isCorrect = true;
                }
    
                $totalMarks += 1;
            } elseif ($question->type === 'open_ended' && $studentAnswer) {
                $maxMark = $question->max_mark ?? 1; // Default to 1 if not set
                $correctAnswer = strtolower(trim($question->correct_answer));
                $studentResponse = strtolower(trim($studentAnswer->answer_text));
    
                $studentResponseText = $studentAnswer->answer_text;
                $correctResponseText = $question->correct_answer;
    
                $similarity = levenshtein($correctAnswer, $studentResponse);
                $maxLen = max(strlen($correctAnswer), strlen($studentResponse));
                $matchPercentage = ($maxLen > 0) ? ((1 - ($similarity / $maxLen)) * 100) : 0;
    
                if ($matchPercentage >= 80) {
                    $earnedMarks += $maxMark;
                    $correctAnswers++;
                    $isCorrect = true;
                } elseif ($matchPercentage >= 50) {
                    $earnedMarks += $maxMark / 2;
                }
    
                $totalMarks += $maxMark;
            }
    
            $questionsWithAnswers[] = [
                'question' => $question->question_text,
                'studentAnswer' => $studentResponseText ?? 'No Answer',
                'correctAnswer' => $correctResponseText ?? 'N/A',
                'isCorrect' => $isCorrect
            ];
        }
    
        $score = ($totalMarks > 0) ? round(($earnedMarks / $totalMarks) * 100, 2) : 0;
    
        return view('quizzes.students.results', [
            'attempt' => $attempt,
            'score' => $score,
            'totalMarks' => $totalMarks,
            'earnedMarks' => $earnedMarks,
            'correctAnswers' => $correctAnswers,
            'questionsWithAnswers' => $questionsWithAnswers
        ]);
    }

}
