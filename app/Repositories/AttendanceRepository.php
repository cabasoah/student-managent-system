<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Attendance;
use App\Interfaces\AttendanceInterface;
use Illuminate\Support\Facades\DB;

class AttendanceRepository implements AttendanceInterface {
    public function saveAttendance($data) {
        try {
            DB::beginTransaction(); // Start transaction
    
            // Validate that student IDs exist
            if (!isset($data['student_ids']) || empty($data['student_ids'])) {
                throw new \Exception("Student IDs are required.");
            }
    
            $input = $this->prepareInput($data);
            Attendance::insert($input); // Bulk insert
    
            DB::commit(); // Commit transaction
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if any error occurs
            throw new \Exception('Failed to save attendance. ' . $e->getMessage());
        }
    }

    public function prepareInput(array $data) {
        $now = Carbon::now()->toDateTimeString();
    
        return array_map(function ($student_id) use ($data, $now) {
            return [
                'status'     => $data['status'][$student_id] ?? 'off',
                'class_id'   => $data['class_id'],
                'student_id' => $student_id,
                'section_id' => $data['section_id'],
                'course_id'  => $data['course_id'],
                'session_id' => $data['session_id'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $data['student_ids']);
    }

    public function getSectionAttendance($class_id, $section_id, $session_id, $limit = 10) {
        try {
            return Attendance::select(['id', 'student_id', 'status', 'created_at'])
                            ->with('student:id,first_name,last_name') // Only fetch necessary columns
                            ->where(compact('class_id', 'section_id', 'session_id'))
                            ->whereDate('created_at', Carbon::today())
                            ->orderBy('created_at', 'desc')
                            ->paginate($limit);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get section attendance. ' . $e->getMessage());
        }
    }

    public function getCourseAttendance($class_id, $course_id, $session_id, $limit = 10) {
        try {
            return Attendance::select(['id', 'student_id', 'status', 'created_at'])
                            ->with('student:id,name') // Optimize related data fetching
                            ->where(compact('class_id', 'course_id', 'session_id'))
                            ->whereDate('created_at', Carbon::today())
                            ->orderBy('created_at', 'desc')
                            ->paginate($limit);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get course attendance. ' . $e->getMessage());
        }
    }

    public function getStudentAttendance($session_id, $student_id, $limit = 10) {
        try {
            return Attendance::select(['id', 'section_id', 'course_id', 'status', 'created_at'])
                            ->with([
                                'section:id,section_name',
                                'course:id,course_name'
                            ]) // Fetch only necessary columns from related tables
                            ->where(compact('session_id', 'student_id'))
                            ->orderBy('created_at', 'desc')
                            ->paginate($limit);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get student attendance. ' . $e->getMessage());
        }
    }
}