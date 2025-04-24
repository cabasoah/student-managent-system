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
// Duplication of the getGrade function with a different name
if (!function_exists('getGradeOne')) {
    function getGradeOne($score)
    {

        $percentage = $score;

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

if (!function_exists('getGPA')) {
    function getGPA($score)
    {

        $percentage = $score;

        if ($percentage >= 80) {
            return 4.0;
        } elseif ($percentage >= 75) {
            return 3.7;
        } elseif ($percentage >= 70) {
            return 3.3;
        } elseif ($percentage >= 65) {
            return 3.0;
        } elseif ($percentage >= 60) {
            return 2.7;
        } elseif ($percentage >= 55) {
            return 2.3;
        } elseif ($percentage >= 50) {
            return 2.0;
        } else {
            return 0.0;
        }
    }
}
