<?php

if (!function_exists('getGrade')) {
    function getGrade($score, $totalMarks)
    {
        if ($totalMarks == 0) {
            return 'N/A'; // Avoid division by zero
        }

        $percentage = ($score / $totalMarks) * 100;

        if ($percentage >= 80) {
            return 'A';
        } elseif ($percentage >= 75) {
            return 'B+';
        } elseif ($percentage >= 70) {
            return 'B';
        } elseif ($percentage >= 65) {
            return 'C+';
        } elseif ($percentage >= 60) {
            return 'C';
        } elseif ($percentage >= 55) {
            return 'D+';
        } elseif ($percentage >= 50) {
            return 'D';
        } else {
            return 'F';
        }
    }
}
