<?php

namespace App\Repositories;

use App\Models\Course;
use App\Models\Semester;
use App\Interfaces\CourseInterface;

class CourseRepository implements CourseInterface {
    public function create($data) {
        try {
            return Course::create([
                'course_name' => $data['course_name'],
                'course_type' => $data['course_type'],
                'class_id'    => $data['class_id'],
                'session_id'  => $data['session_id'],
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create course: ' . $e->getMessage());
        }
    }

    public function getAll($session_id, $limit = 10) {
        return Course::select(['id', 'course_name', 'course_type', 'class_id'])
                     ->where('session_id', $session_id)
                     ->orderBy('course_name', 'asc')
                     ->paginate($limit);
    }

    public function getByClassId($class_id, $limit = 10) {
        return Course::select(['id', 'course_name', 'course_type', 'session_id'])
                     ->where('class_id', $class_id)
                     ->orderBy('course_name', 'asc')
                     ->paginate($limit);
    }

    public function findById($course_id) {
        return Course::select(['id', 'course_name', 'course_type', 'class_id', 'session_id'])
                     ->findOrFail($course_id);
    }

    public function update($data) {
        try {
            $course = Course::findOrFail($data['course_id']);
            $course->update([
                'course_name' => $data['course_name'],
                'course_type' => $data['course_type'],
            ]);
            return $course;
        } catch (\Exception $e) {
            throw new \Exception('Failed to update course: ' . $e->getMessage());
        }
    }
}