<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Option;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class QuizQuestionsImport implements ToCollection
{
    protected $quizId;
    protected $teacherId;

    public function __construct($quizId, $teacherId)
    {
        $this->quizId = $quizId;
        $this->teacherId = $teacherId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip header row

            $question = Question::create([
                'quiz_id' => $this->quizId,
                'teacher_id' => $this->teacherId,
                'question_text' => $row[0],
                'type' => $row[1],
                'correct_answer' => $row[2],
                'max_mark' => $row[3],
            ]);

            if (in_array($row[1], ['single_choice', 'multiple_choice'])) {
                for ($i = 4; $i <= 7; $i++) {
                    if (!empty($row[$i])) {
                        Option::create([
                            'question_id' => $question->id,
                            'option_text' => $row[$i],
                            'is_correct' => ($i == 2), // Assume first option is correct
                        ]);
                    }
                }
            }
        }
    }

}
