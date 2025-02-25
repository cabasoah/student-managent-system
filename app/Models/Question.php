<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SchoolSession as Session;

class Question extends Model
{
    use HasFactory;
    
    protected $fillable = ['quiz_id', 'teacher_id', 'class_id', 'section_id', 'semester_id', 'session_id', 'question_text', 'type', 'correct_answer', 'max_mark'];

    // A question belongs to a quiz
    public function quiz() {
        return $this->belongsTo(Quiz::class,'quiz_id');
    }

    // A question belongs to a teacher
    public function teacher() {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // A question belongs to a class
    public function class() {
        return $this->belongsTo(SchoolClass::class, 'class_id'); 
    }

    // A question belongs to a section
    public function section() {
        return $this->belongsTo(Section::class, 'section_id'); 
    }

    // A question belongs to a semester
    public function semester() {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    // A question belongs to a session
    public function session() {
        return $this->belongsTo(Session::class, 'session_id');
    }

    // A question has many options
    public function options() {
        return $this->hasMany(Option::class,'question_id');
    }

    // A question has many student answers
    public function studentAnswers() {
        return $this->hasMany(StudentAnswer::class);
    }

}
