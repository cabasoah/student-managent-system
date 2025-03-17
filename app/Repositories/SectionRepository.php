<?php

namespace App\Repositories;

use App\Models\Section;
use App\Interfaces\SectionInterface;

class SectionRepository implements SectionInterface {
    public function create($request) {
        try {
            return Section::create($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create School Section: ' . $e->getMessage());
        }
    }

    public function getAllBySession($session_id) {
        return Section::where('session_id', $session_id)->get();
    }

    public function getAllByClassId($class_id) {
        return Section::where('class_id', $class_id)->get();
    }

    public function findById($section_id) {
        return Section::find($section_id);
    }

    public function update($request) {
        try {
            $section = Section::find($request->section_id);
            if (!$section) {
                throw new \Exception('Section not found.');
            }
            return $section->update([
                'section_name' => $request->section_name,
                'room_no'      => $request->room_no,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update School Section: ' . $e->getMessage());
        }
    }
}