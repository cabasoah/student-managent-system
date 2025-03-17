<?php

namespace App\Repositories;

use App\Models\Notice;

class NoticeRepository {
    public function store($data) {
        try {
            return Notice::create([
                'notice'     => $data['notice'],
                'session_id' => $data['session_id'],
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to save notice: ' . $e->getMessage());
        }
    }

    public function getAll($session_id, $perPage = 3) {
        try {
            return Notice::where('session_id', $session_id)
                         ->orderByDesc('id')
                         ->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve notices: ' . $e->getMessage());
        }
    }
}