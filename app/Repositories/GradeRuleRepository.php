<?php

namespace App\Repositories;

use App\Models\GradeRule;

class GradeRuleRepository {
    public function store($data) {
        try {
            return GradeRule::create([
                'grading_system_id' => $data['grading_system_id'],
                'session_id'        => $data['session_id'],
                'grade'             => $data['grade'],
                'min_score'         => $data['min_score'],
                'max_score'         => $data['max_score'],
                'remark'            => $data['remark'] ?? null,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create grading system rule: ' . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $gradeRule = GradeRule::findOrFail($id);
            return $gradeRule->delete();
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete grading system rule: ' . $e->getMessage());
        }
    }

    public function getAll($session_id, $grading_system_id) {
        try {
            return GradeRule::with('gradingSystem')
                            ->where('grading_system_id', $grading_system_id)
                            ->where('session_id', $session_id)
                            ->orderBy('min_score', 'desc')
                            ->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve grading system rules: ' . $e->getMessage());
        }
    }
}