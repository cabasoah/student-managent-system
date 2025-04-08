<?php

namespace App\Repositories;

use App\Models\Syllabus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SyllabusRepository {
    public function store($request) {
        try {
            // Ensure a file is provided before storing
            if (!isset($request['file'])) {
                throw new \Exception('No file provided.');
            }

            // Store file in the "public/syllabi" directory
            $path = Storage::disk('public')->put('syllabi', $request['file']);
            
            if (!$path) {
                throw new \Exception('Failed to upload syllabus file.');
            }

            // Create Syllabus record
            return Syllabus::create([
                'syllabus_name'      => $request['syllabus_name'],
                'syllabus_file_path' => $path,
                'class_id'           => $request['class_id'],
                'course_id'          => $request['course_id'],
                'session_id'         => $request['session_id']
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create syllabus. ' . $e->getMessage());
        }
    }

    public function getByClass($class_id) {
        $syllabi = Syllabus::where('class_id', $class_id)->get();
        
        if ($syllabi->isEmpty()) {
            throw new ModelNotFoundException('No syllabus found for this class.');
        }

        return $syllabi;
    }

    public function getByCourse($course_id) {
        $syllabi = Syllabus::where('course_id', $course_id)->get();
        // dd($syllabi);
        // if ($syllabi->isEmpty()) {
        //     throw new ModelNotFoundException('No syllabus found for this course.');
        // }

        return $syllabi;
    }
}