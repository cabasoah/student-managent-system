<?php

namespace App\Exports;

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
        return StudentQuizAttempt::where('quiz_id', $this->quizId)
            ->join('users', 'student_quiz_attempts.student_id', '=', 'users.id')
            ->select(
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as student_name"),
                'student_quiz_attempts.score',
                'student_quiz_attempts.created_at'
            )
            ->orderBy('student_quiz_attempts.created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ["Student Name", "Score", "Attempt Date"];
    }
}
