<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Option;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class QuizQuestionsImport implements ToCollection
{
    protected $quizId;

    public function __construct($quizId)
    {
        $this->quizId = $quizId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip header row

            $question = Question::create([
                'quiz_id' => $this->quizId,
                'question_text' => $row[0],
                'type' => $row[1],
            ]);

            if (in_array($row[1], ['single_choice', 'multiple_choice'])) {
                for ($i = 2; $i <= 5; $i++) {
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
