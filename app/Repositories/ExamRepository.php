<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Interfaces\ExamInterface;

class ExamRepository implements ExamInterface {
    public function create($data) {
        try {
            return Exam::create([
                'exam_name'   => $data['exam_name'],
                'session_id'  => $data['session_id'],
                'semester_id' => $data['semester_id'],
                'class_id'    => $data['class_id'],
                'course_id'   => $data['course_id'],
                'date'        => $data['date'] ?? null,  // Ensure a valid date
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create exam: ' . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $exam = Exam::findOrFail($id);
            return $exam->delete();
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete exam: ' . $e->getMessage());
        }
    }

    public function getAll($session_id, $semester_id = null, $class_id = null, $limit = 10) {
        try {
            // Assign default semester and class if not provided
            if (!$semester_id) {
                $semester_id = Semester::where('session_id', $session_id)->value('id');
            }
            if (!$class_id) {
                $class_id = SchoolClass::where('session_id', $session_id)->value('id');
            }

            return Exam::with('course')
                        ->where('session_id', $session_id)
                        ->where('semester_id', $semester_id)
                        ->where('class_id', $class_id)
                        ->orderBy('date', 'desc')
                        ->paginate($limit);
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve exams: ' . $e->getMessage());
        }
    }

}