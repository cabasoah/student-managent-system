<?php

namespace App\Repositories;

use App\Models\Semester;
use App\Models\AssignedTeacher;
use App\Interfaces\AssignedTeacherInterface;

class AssignedTeacherRepository implements AssignedTeacherInterface {

    public function assign($request) {
        try {
            AssignedTeacher::create($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to assign teacher. '.$e->getMessage());
        }
    }

    public function getTeacherCourses($session_id, $teacher_id, $semester_id) {
        if ($semester_id == 0) {
            $semester_id = Semester::where('session_id', $session_id)->pluck('id')->first();
            if (!$semester_id) return collect(); // Return empty collection if no semester found
        }
    
        return AssignedTeacher::with(['course', 'schoolClass', 'section'])
                    ->where(compact('session_id', 'teacher_id', 'semester_id'))
                    ->get();
    }

    public function getAssignedTeacher($session_id, $semester_id, $class_id, $section_id, $course_id) {
        if ($semester_id == 0) {
            $semester_id = Semester::where('session_id', $session_id)->pluck('id')->first();
            if (!$semester_id) return null; // Prevents error if no semester found
        }
    
        return AssignedTeacher::where(compact('session_id', 'semester_id', 'class_id', 'section_id', 'course_id'))
                    ->first();
    }
    
}