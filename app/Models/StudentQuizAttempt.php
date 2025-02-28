<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SchoolSession as Session;

class StudentQuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'quiz_id', 'class_id', 'section_id', 'semester_id', 'session_id', 'score','started_at'];

    // A quiz attempt belongs to a student
    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }

    // A quiz attempt belongs to a quiz
    public function quiz() {
        return $this->belongsTo(Quiz::class);
    }

    // A quiz attempt belongs to a class
    public function class() {
        return $this->belongsTo(SchoolClass::class, 'class_id'); 
    }

    // A quiz attempt belongs to a section
    public function section() {
        return $this->belongsTo(Section::class, 'section_id'); 
    }

    // A quiz attempt belongs to a semester
    public function semester() {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    // A quiz attempt belongs to a session
    public function session() {
        return $this->belongsTo(Session::class, 'session_id');
    }

    // A quiz attempt has many student answers
    public function answers() {
        return $this->hasMany(StudentAnswer::class, 'attempt_id');
    }
}
