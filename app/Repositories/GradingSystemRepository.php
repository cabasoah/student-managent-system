<?php

namespace App\Repositories;

use App\Models\GradingSystem;

class GradingSystemRepository {
    public function store($data) {
        try {
            return GradingSystem::create([
                'session_id'  => $data['session_id'],
                'semester_id' => $data['semester_id'],
                'class_id'    => $data['class_id'],
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create grading system: ' . $e->getMessage());
        }
    }

    public function getAll($session_id) {
        try {
            return GradingSystem::with(['semester', 'schoolClass'])
                                ->where('session_id', $session_id)
                                ->orderBy('created_at', 'desc')
                                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve grading systems: ' . $e->getMessage());
        }
    }

    public function getGradingSystem($session_id, $semester_id, $class_id) {
        try {
            return GradingSystem::with(['semester', 'schoolClass'])
                                ->where([
                                    ['session_id', '=', $session_id],
                                    ['semester_id', '=', $semester_id],
                                    ['class_id', '=', $class_id],
                                ])
                                ->firstOrFail();
        } catch (\Exception $e) {
            throw new \Exception('Grading system not found: ' . $e->getMessage());
        }
    }
}