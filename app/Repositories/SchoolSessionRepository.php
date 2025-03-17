<?php

namespace App\Repositories;

use App\Models\SchoolSession;
use App\Interfaces\SchoolSessionInterface;

class SchoolSessionRepository implements SchoolSessionInterface {
    public function getLatestSession() {
        return SchoolSession::latest()->first() ?? (object) ['id' => 0];
    }

    public function getAll() {
        return SchoolSession::all();
    }

    public function getPreviousSession() {
        $lastTwoSessions = SchoolSession::orderByDesc('id')->take(2)->get();
        return $lastTwoSessions->count() < 2 ? null : $lastTwoSessions[1];
    }

    public function create($request) {
        try {
            return SchoolSession::create($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create School Session: ' . $e->getMessage());
        }
    }

    public function browse($request) {
        try {
            $latestSession = $this->getLatestSession();
            if (session()->has('browse_session_id') && ($request['session_id'] == $latestSession->id)) {
                session()->forget(['browse_session_id', 'browse_session_name']);
            } else {
                $session = $this->getSessionById($request['session_id']);
                if (!$session) {
                    throw new \Exception('Invalid session ID.');
                }
                session([
                    'browse_session_id' => $session->id,
                    'browse_session_name' => $session->session_name
                ]);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to set School Session for browsing: ' . $e->getMessage());
        }
    }

    public function getSessionById($id) {
        return SchoolSession::find($id);
    }
}