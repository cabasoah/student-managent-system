<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('teacher_id'); // Added to track the teacher creating the quiz
            $table->unsignedInteger('semester_id'); // Added to link with semester
            $table->unsignedInteger('class_id'); // Added to link with class
            $table->unsignedInteger('section_id'); // Added to link with section
            $table->unsignedInteger('course_id'); // Already present
            $table->unsignedInteger('session_id'); // Added to track academic session
            $table->string('title'); // Quiz title
            $table->text('description')->nullable(); // Quiz description
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quizzes');
    }
}
