<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SchoolSession as Session;
class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'course_id', 'teacher_id', 'class_id', 'section_id', 'semester_id', 'session_id','duration', 'is_visible_to_student'];

    // A quiz belongs to a course
    public function course() {
        return $this->belongsTo(Course::class);
    }

    // A quiz belongs to a teacher
    public function teacher() {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // A quiz belongs to a class
    public function class() {
        return $this->belongsTo(SchoolClass::class, 'class_id'); 
    }

    // A quiz belongs to a section
    public function section() {
        return $this->belongsTo(Section::class, 'section_id'); 
    }

    // A quiz belongs to a semester
    public function semester() {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    // A quiz belongs to a session
    public function session() {
        return $this->belongsTo(Session::class, 'session_id');
    }

    // A quiz has many questions
    public function questions() {
        return $this->hasMany(Question::class);
    }

    // A quiz has many attempts from students
    public function attempts() {
        return $this->hasMany(StudentQuizAttempt::class);
    }
}
