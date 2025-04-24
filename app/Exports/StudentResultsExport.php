<?php

namespace App\Exports;

use App\Models\Quiz;
use App\Models\StudentQuizAttempt;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentResultsExport implements FromCollection, WithHeadings
{
    protected $quizId;

    public function __construct($quizId)
    {
        $this->quizId = $quizId;
    }

    public function collection()
    {
        $quiz = Quiz::with(['questions.options'])->findOrFail($this->quizId);

        // Get all attempts with answers and options
        $studentAttempts = StudentQuizAttempt::with(['student', 'answers.option'])
            ->where('quiz_id', $this->quizId)
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
        return collect($studentResults)->map(function ($result, $index) {
            return [
                'number' => $index + 1,
                'student_name' => $result['student_name'],
                'total_marks' => $result['total_marks'],
                'grade' => getGrade($result['score'], $result['total_marks']),
                'points' => getGPA($result['score'], $result['total_marks']),
            ];
        });
    }

    public function headings(): array
    {
        return ["Number", "Student Name", "Total Marks", "Grade", "Points"];
    }
}
