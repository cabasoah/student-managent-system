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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

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
            'attemptedQuizzes' => $attemptedQuizzes,
            'course_id' => $course_id
        ]);
    }

    public function quizInstructions($quiz_id) {
        $quiz = Quiz::with('questions.options')->findOrFail($quiz_id);
        $totalMarks = $quiz->questions->reduce(function ($carry, $question) {
            if (in_array($question->type, ['single_choice', 'multiple_choice'])) {
                return $carry + 1; // Single & multiple-choice questions count as 1 mark each
            } elseif ($question->type === 'open_ended') {
                return $carry + $question->max_mark; // Open-ended questions contribute their max mark
            }
            return $carry;
        }, 0);
        
        $quiz->total_marks = $totalMarks;
        return view('quizzes.students.quiz_instructions', compact('quiz'));
    }
    

    public function attemptQuiz($quiz_id)
    {
        $student_id = Auth::user()->id;
        $quiz = Quiz::with(['questions' => function($query) {
            $query->inRandomOrder()->with('options');
        }])->findOrFail($quiz_id);
        

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
            'option_id' => 'nullable|exists:options,id', // For single_choice
            'option_ids' => 'nullable|array', // For multiple_choice
            'option_ids.*' => 'exists:options,id',
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
    
        // Handle multiple choice separately
        if ($question->type === 'multiple_choice') {
            // Delete old answers for this question
            StudentAnswer::where('attempt_id', $attempt->id)
                ->where('question_id', $question->id)
                ->delete();
    
            if (!empty($request->option_ids) && is_array($request->option_ids)) {
                foreach ($request->option_ids as $optionId) {
                    StudentAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'option_id' => $optionId,
                        'answer_text' => null,
                        'class_id' => $request->class_id ?? $attempt->class_id,
                        'section_id' => $request->section_id ?? $attempt->section_id,
                        'semester_id' => $request->semester_id ?? $attempt->semester_id,
                        'session_id' => $request->session_id ?? $attempt->session_id,
                    ]);
                }
            }
    
        } else {
            // Handle single_choice and open_ended
            StudentAnswer::updateOrCreate(
                [
                    'attempt_id' => $request->attempt_id,
                    'question_id' => $request->question_id,
                ],
                [
                    'option_id' => $question->type === 'open_ended' ? null : $request->option_id,
                    'answer_text' => $request->answer,
                    'class_id' => $request->class_id ?? $attempt->class_id,
                    'section_id' => $request->section_id ?? $attempt->section_id,
                    'semester_id' => $request->semester_id ?? $attempt->semester_id,
                    'session_id' => $request->session_id ?? $attempt->session_id,
                ]
            );
        }
    
        return response()->json(['success' => true, 'message' => 'Answer saved successfully']);
    }

    public function submitQuiz(Request $request)
    {
        set_time_limit(120); // Temporary execution time increase
    
        $request->validate([
            'attempt_id' => 'required|exists:student_quiz_attempts,id',
            'class_id' => 'nullable|integer|exists:school_classes,id',
            'section_id' => 'nullable|integer|exists:sections,id',
            'semester_id' => 'nullable|integer|exists:semesters,id',
            'session_id' => 'nullable|integer|exists:school_sessions,id',
        ]);
    
        $attempt = StudentQuizAttempt::findOrFail($request->attempt_id);
    
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
    
        if ($attempt->student_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized submission'], 403);
        }
    
        $score = 0;
    
        // Fetch all student answers once
        // $studentAnswers = $attempt->answers()->get()->keyBy('question_id');
        $studentAnswers = $attempt->answers()->get();
        foreach ($quiz->questions as $question) {
            if ($question->type == 'multiple_choice') {
                $selectedOptions = $studentAnswers
                    ->where('question_id', $question->id)
                    ->pluck('option_id')
                    ->sort()
                    ->values()
                    ->toArray();
        
                $correctOptions = collect($question->options)
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->toArray();
        
                if ($selectedOptions === $correctOptions) {
                    $score += 1;
                }
            } elseif ($question->type == 'single_choice') {
                $studentAnswer = $studentAnswers->firstWhere('question_id', $question->id);
                if ($studentAnswer && $question->options->where('id', $studentAnswer->option_id)->first()?->is_correct) {
                    $score += 1;
                }
            } elseif ($question->type == 'open_ended' && !empty($question->correct_answer)) {
                $studentAnswer = $studentAnswers->firstWhere('question_id', $question->id);
                if ($studentAnswer) {
                    $studentText = strtolower(trim($studentAnswer->answer_text));
                    $correctText = strtolower(trim($question->correct_answer));
        
                    similar_text($studentText, $correctText, $similarityPercentage);
        
                    $marksAwarded = 0;
                    if ($similarityPercentage >= 90) {
                        $marksAwarded = $question->max_mark;
                    } elseif ($similarityPercentage >= 70) {
                        $marksAwarded = $question->max_mark * 0.75;
                    } elseif ($similarityPercentage >= 50) {
                        $marksAwarded = $question->max_mark * 0.5;
                    } elseif ($similarityPercentage >= 25) {
                        $marksAwarded = $question->max_mark * 0.25;
                    } elseif ($similarityPercentage >= 10) {
                        $marksAwarded = $question->max_mark * 0.1;
                    }
        
                    $studentAnswer->update(['marks_awarded' => $marksAwarded]);
        
                    $score += $marksAwarded;
                }
            }
        }
        
    
        // foreach ($quiz->questions as $question) {
        //     $studentAnswer = $studentAnswers->get($question->id);
    
        //     if ($studentAnswer) {
        //         if ($question->type == 'single_choice' || $question->type == 'multiple_choice') {
        //             $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
        //             $selectedOptions = is_array($studentAnswer->option_id) ? $studentAnswer->option_id : [$studentAnswer->option_id];
    
        //             if ($question->type == 'multiple_choice') {
        //                 $selectedOptions = $studentAnswers->where('question_id', $question->id)->pluck('option_id')->toArray();
        //             }
    
        //             if ($selectedOptions == $correctOptions) {
        //                 $score += 1;
        //             }
        //         } elseif ($question->type == 'open_ended' && !empty($question->correct_answer)) {
        //             $studentText = strtolower(trim($studentAnswer->answer_text));
        //             $correctText = strtolower(trim($question->correct_answer));
    
        //             // Faster alternative to levenshtein
        //             similar_text($studentText, $correctText, $similarityPercentage);
    
        //             $marksAwarded = 0;
        //             if ($similarityPercentage >= 90) {
        //                 $marksAwarded = $question->max_mark;
        //             } elseif ($similarityPercentage >= 70) {
        //                 $marksAwarded = $question->max_mark * 0.75;
        //             } elseif ($similarityPercentage >= 50) {
        //                 $marksAwarded = $question->max_mark * 0.5;
        //             } elseif ($similarityPercentage >= 25) {
        //                 $marksAwarded = $question->max_mark * 0.25;
        //             } elseif ($similarityPercentage >= 10) {
        //                 $marksAwarded = $question->max_mark * 0.1;
        //             }

        //             //Store the awarded marks in `student_answers`
        //             $studentAnswer->update(['marks_awarded' => $marksAwarded]);

        //             $score += $marksAwarded;
    
                    
        //         }
        //     }
        // }
    
        // Save the total score
        $attempt->update([
            'score' => $score,
            'class_id' => $request->class_id ?? $attempt->class_id,
            'section_id' => $request->section_id ?? $attempt->section_id,
            'semester_id' => $request->semester_id ?? $attempt->semester_id,
            'session_id' => $request->session_id ?? $attempt->session_id,
        ]);
    
        return response()->json([
            'message' => 'Quiz submitted successfully',
            'score' => $score
        ]);
    }
    
    
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

        foreach ($attempt->quiz->questions as $question) {
            $studentAnswers = $attempt->answers->where('question_id', $question->id);
        
            $studentResponseText = null;
            $correctResponseText = null;
            $isCorrect = false;
            $awardedMark = 0;
        
            if ($question->type === 'multiple_choice') {
                $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
                $studentSelectedOptions = $studentAnswers->pluck('option_id')->toArray();
        
                // Get text of student-selected options
                $selectedTexts = $question->options->whereIn('id', $studentSelectedOptions)->pluck('option_text')->toArray();
                $studentResponseText = !empty($selectedTexts) ? implode(', ', $selectedTexts) : 'No Answer';
        
                // Get correct option texts
                $correctResponseText = $question->options->where('is_correct', true)->pluck('option_text')->join(', ') ?? 'N/A';
        
                if (count(array_diff($studentSelectedOptions, $correctOptions)) === 0 &&
                    count(array_diff($correctOptions, $studentSelectedOptions)) === 0) {
                    $correctAnswers++;
                    $earnedMarks += 1;
                    $isCorrect = true;
                }
        
                $totalMarks += 1;
            } elseif ($question->type === 'single_choice') {
                $answer = $studentAnswers->first();
        
                $studentResponseText = $answer && $answer->option ? $answer->option->option_text : 'No Answer';
                $correctResponseText = $question->options->where('is_correct', true)->pluck('option_text')->join(', ') ?? 'N/A';
        
                if ($answer && $answer->option_id && $question->options->where('is_correct', true)->pluck('id')->contains($answer->option_id)) {
                    $correctAnswers++;
                    $earnedMarks += 1;
                    $isCorrect = true;
                }
        
                $totalMarks += 1;
            } elseif ($question->type === 'open_ended' && $studentAnswers->first()) {
                $maxMark = $question->max_mark ?? 1;
                $awardedMark = $studentAnswers->first()->marks_awarded ?? 0;
        
                $correctAnswer = strtolower(trim($question->correct_answer));
                $studentResponse = strtolower(trim($studentAnswers->first()->answer_text));
        
                $studentResponseText = $studentAnswers->first()->answer_text;
                $correctResponseText = $question->correct_answer;
        
                $earnedMarks += $awardedMark;
                if ($awardedMark >= $maxMark) {
                    $correctAnswers++;
                    $isCorrect = true;
                }
        
                $totalMarks += $maxMark;
            }
        
            $questionsWithAnswers[] = [
                'question' => $question->question_text,
                'questionType' => $question->type,
                'studentAnswer' => $studentResponseText ?? 'No Answer',
                'correctAnswer' => $correctResponseText ?? 'N/A',
                'isCorrect' => $isCorrect,
                'awardedMark' => $awardedMark,
                'maxMark' => $question->max_mark ?? 1,
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

    public function showQuizes($id)
    {
        $quizAttempts = StudentQuizAttempt::where('student_id', $id)
            ->with(['quiz.questions'])
            ->get();

        $quizSummaries = [];

        foreach ($quizAttempts as $attempt) {
            $totalMarks = 0;

            foreach ($attempt->quiz->questions as $question) {
                if (in_array($question->type, ['single_choice', 'multiple_choice'])) {
                    $totalMarks += 1;
                } elseif ($question->type === 'open_ended') {
                    $totalMarks += $question->max_mark ?? 1;
                }
            }

            $quizSummaries[] = [
                'attempt_id' => $attempt->id,
                'quiz_title' => $attempt->quiz->title,
                'total_marks' => $totalMarks,
                'score' => $attempt->score,
                'date_taken' => $attempt->created_at->format('d M Y'),
            ];
        }

        return view('students.quizzes', compact('quizSummaries'));
    }

    public function preview(StudentQuizAttempt $quizAttempt)
    {
        $attempt = $quizAttempt->load(['quiz.questions.options', 'answers']);
        return view('students.quizzes.preview', compact('attempt'));
    }

    public function downloadPdf(StudentQuizAttempt $quizAttempt)
    {
        $attempt = StudentQuizAttempt::with(['quiz.questions.options', 'answers.option'])->find($quizAttempt->id);
       
        if (!$attempt) {
            return redirect()->route('home')->with('error', 'Quiz attempt not found.');
        }

        $totalMarks = 0;
        $earnedMarks = 0;
        $correctAnswers = 0;

        $questionsWithAnswers = [];

        foreach ($attempt->quiz->questions as $question) {
            $studentAnswers = $attempt->answers->where('question_id', $question->id);
        
            $studentResponseText = null;
            $correctResponseText = null;
            $isCorrect = false;
            $awardedMark = 0;
        
            if ($question->type === 'multiple_choice') {
                $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
                $studentSelectedOptions = $studentAnswers->pluck('option_id')->toArray();
        
                // Get text of student-selected options
                $selectedTexts = $question->options->whereIn('id', $studentSelectedOptions)->pluck('option_text')->toArray();
                $studentResponseText = !empty($selectedTexts) ? implode(', ', $selectedTexts) : 'No Answer';
        
                // Get correct option texts
                $correctResponseText = $question->options->where('is_correct', true)->pluck('option_text')->join(', ') ?? 'N/A';
        
                if (count(array_diff($studentSelectedOptions, $correctOptions)) === 0 &&
                    count(array_diff($correctOptions, $studentSelectedOptions)) === 0) {
                    $correctAnswers++;
                    $earnedMarks += 1;
                    $isCorrect = true;
                }
        
                $totalMarks += 1;
            } elseif ($question->type === 'single_choice') {
                $answer = $studentAnswers->first();
        
                $studentResponseText = $answer && $answer->option ? $answer->option->option_text : 'No Answer';
                $correctResponseText = $question->options->where('is_correct', true)->pluck('option_text')->join(', ') ?? 'N/A';
        
                if ($answer && $answer->option_id && $question->options->where('is_correct', true)->pluck('id')->contains($answer->option_id)) {
                    $correctAnswers++;
                    $earnedMarks += 1;
                    $isCorrect = true;
                }
        
                $totalMarks += 1;
            } elseif ($question->type === 'open_ended' && $studentAnswers->first()) {
                $maxMark = $question->max_mark ?? 1;
                $awardedMark = $studentAnswers->first()->marks_awarded ?? 0;
        
                $correctAnswer = strtolower(trim($question->correct_answer));
                $studentResponse = strtolower(trim($studentAnswers->first()->answer_text));
        
                $studentResponseText = $studentAnswers->first()->answer_text;
                $correctResponseText = $question->correct_answer;
        
                $earnedMarks += $awardedMark;
                if ($awardedMark >= $maxMark) {
                    $correctAnswers++;
                    $isCorrect = true;
                }
        
                $totalMarks += $maxMark;
            }
        
            $questionsWithAnswers[] = [
                'question' => $question->question_text,
                'questionType' => $question->type,
                'studentAnswer' => $studentResponseText ?? 'No Answer',
                'correctAnswer' => $correctResponseText ?? 'N/A',
                'isCorrect' => $isCorrect,
                'awardedMark' => $awardedMark,
                'maxMark' => $question->max_mark ?? 1,
            ];
        }
        

        $score = ($totalMarks > 0) ? round(($earnedMarks / $totalMarks) * 100, 2) : 0;
        $studentName = $attempt->student->first_name . ' ' . $attempt->student->last_name; 
        $pdfName = Str::slug($studentName . ' - ' . $attempt->quiz->title) . '.pdf';
        $pdf = Pdf::loadView('students.quizzes_pdf', compact('attempt', 'questionsWithAnswers', 'score','earnedMarks','totalMarks','correctAnswers','studentName'));
        return $pdf->download($pdfName);
    }

}
