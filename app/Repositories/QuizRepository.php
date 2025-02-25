<?php

namespace App\Repositories;

use App\Models\Quiz;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Assert;

class QuizRepository {
    public function getQuizzesForStudent($session_id, $course_id)
    {
        return Quiz::where('course_id', $course_id)
                   ->where('session_id', $session_id)
                   ->where('is_visible_to_student', true)
                   ->get();
    }
}