<?php

namespace App\Repositories;

use App\Interfaces\ExamRuleInterface;
use App\Models\ExamRule;

class ExamRuleRepository implements ExamRuleInterface {
    public function create($data) {
        try {
            return ExamRule::create([
                'exam_id'                   => $data['exam_id'],
                'session_id'                => $data['session_id'],
                'total_marks'               => $data['total_marks'],
                'pass_marks'                => $data['pass_marks'],
                'marks_distribution_note'   => $data['marks_distribution_note'] ?? null,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create exam rule: ' . $e->getMessage());
        }
    }

    public function update($data) {
        try {
            $examRule = ExamRule::findOrFail($data['exam_rule_id']);
            return $examRule->update([
                'total_marks'               => $data['total_marks'],
                'pass_marks'                => $data['pass_marks'],
                'marks_distribution_note'   => $data['marks_distribution_note'] ?? $examRule->marks_distribution_note,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update exam rule: ' . $e->getMessage());
        }
    }

    public function getAll($session_id, $exam_id) {
        try {
            return ExamRule::where('session_id', $session_id)
                            ->where('exam_id', $exam_id)
                            ->orderBy('id', 'desc')
                            ->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve exam rules: ' . $e->getMessage());
        }
    }

    public function getById($exam_rule_id) {
        try {
            return ExamRule::findOrFail($exam_rule_id);
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve exam rule: ' . $e->getMessage());
        }
    }
}