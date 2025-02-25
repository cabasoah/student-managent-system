<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SchoolSession as Session;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['attempt_id', 'question_id', 'option_id', 'answer_text', 'class_id', 'section_id', 'semester_id', 'session_id'];

    // A student answer belongs to a quiz attempt
    public function attempt() {
        return $this->belongsTo(StudentQuizAttempt::class, 'attempt_id');
    }

    // A student answer belongs to a question
    public function question() {
        return $this->belongsTo(Question::class);
    }

    // A student answer may belong to an option (only for multiple-choice questions)
    public function option() {
        return $this->belongsTo(Option::class);
    }

    // A student answer belongs to a class
    public function class() {
        return $this->belongsTo(SchoolClass::class, 'class_id'); 
    }

    // A student answer belongs to a section
    public function section() {
        return $this->belongsTo(Section::class, 'section_id'); 
    }

    // A student answer belongs to a semester
    public function semester() {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    // A student answer belongs to a session
    public function session() {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

}
