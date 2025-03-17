<?php

namespace App\Repositories;

use App\Models\SchoolClass;
use App\Interfaces\SchoolClassInterface;
use App\Models\AssignedTeacher;

class SchoolClassRepository implements SchoolClassInterface {
    public function create($request) {
        try {
            SchoolClass::create($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create School Class: ' . $e->getMessage());
        }
    }

    public function getAllBySession($session_id) {
        return SchoolClass::where('session_id', $session_id)->get();
    }

    public function getAllBySessionAndTeacher($session_id, $teacher_id) {
        return AssignedTeacher::with('schoolClass')
            ->where([
                'teacher_id'  => $teacher_id,
                'session_id'  => $session_id,
            ])
            ->get();
    }

    public function getAllWithCoursesBySession($session_id) {
        return SchoolClass::with(['courses', 'syllabi'])
            ->where('session_id', $session_id)
            ->get();
    }

    public function getClassesAndSections($session_id) {
        try {
            $school_classes = $this->getAllWithCoursesBySession($session_id);
            $sectionRepository = new SectionRepository();
            $school_sections = $sectionRepository->getAllBySession($session_id);

            return [
                'school_classes' => $school_classes,
                'school_sections'=> $school_sections,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve classes and sections: ' . $e->getMessage());
        }
    }

    public function findById($class_id) {
        return SchoolClass::find($class_id);
    }

    public function update($request) {
        try {
            $schoolClass = SchoolClass::find($request->class_id);
            if (!$schoolClass) {
                throw new \Exception('School Class not found.');
            }

            $schoolClass->update([
                'class_name' => $request->class_name,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update School Class: ' . $e->getMessage());
        }
    }
}