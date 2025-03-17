<?php

namespace App\Repositories;

use App\Models\AcademicSetting;
use App\Interfaces\AcademicSettingInterface;

class AcademicSettingRepository implements AcademicSettingInterface {
    public function getAcademicSetting(){
        return cache()->remember('academic_setting', 60 * 60, function () {
            return AcademicSetting::find(1);
        });
    }

    public function updateAttendanceType($request) {
        try {
            AcademicSetting::findOrFail(1)->update($request);
            cache()->forget('academic_setting'); // Clear cache
        } catch (\Exception $e) {
            throw new \Exception('Failed to update attendance type. '.$e->getMessage());
        }
    }

    public function updateFinalMarksSubmissionStatus($request) {
        try {
            $status = !empty($request['marks_submission_status']) ? "on" : "off";
            AcademicSetting::findOrFail(1)->update(['marks_submission_status' => $status]);
            cache()->forget('academic_setting'); // Clear cache
        } catch (\Exception $e) {
            throw new \Exception('Failed to update final marks submission status. '.$e->getMessage());
        }
    }
}