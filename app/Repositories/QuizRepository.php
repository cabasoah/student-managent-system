<?php

namespace App\Repositories;

use App\Models\Quiz;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Assert;

class QuizRepository {
    public function getQuizzesForStudent($session_id, $course_id) {
        try {
            return Quiz::where([
                'session_id'              => $session_id,
                'course_id'               => $course_id,
                'is_visible_to_student'   => true
            ])->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve quizzes: ' . $e->getMessage());
        }
    }
}