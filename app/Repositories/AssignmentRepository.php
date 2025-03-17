<?php

namespace App\Repositories;

use App\Models\Assignment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Assert;
use Illuminate\Support\Facades\DB;

class AssignmentRepository {
    public function store($request) {
        try {
            DB::beginTransaction(); // Start transaction
    
            if (!isset($request['file'])) {
                throw new \Exception("Assignment file is required.");
            }
    
            // Store file and handle potential failure
            $path = Storage::disk('public')->put('assignments', $request['file']);
            if (!$path) {
                throw new \Exception("File upload failed.");
            }
    
            // Save assignment in the database
            $assignment = Assignment::create([
                'assignment_name'      => $request['assignment_name'],
                'assignment_file_path' => $path,
                'teacher_id'           => auth()->id(), //Cleaner than auth()->user()->id
                'class_id'             => $request['class_id'],
                'section_id'           => $request['section_id'],
                'course_id'            => $request['course_id'],
                'semester_id'          => $request['semester_id'],
                'session_id'           => $request['session_id'],
            ]);
    
            DB::commit(); // Commit transaction
            return $assignment;
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if anything fails
            throw new \Exception('Failed to create assignment. ' . $e->getMessage());
        }
    
    }

    public function getAssignments($session_id, $course_id, $limit = 10) {
        return Assignment::select(['id', 'assignment_name', 'assignment_file_path', 'teacher_id', 'created_at'])
                    ->where(compact('session_id', 'course_id'))
                    ->orderBy('created_at', 'desc') // Get latest assignments first
                    ->paginate($limit); // Paginate results
    }
    
}