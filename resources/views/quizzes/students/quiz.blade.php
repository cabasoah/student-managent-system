@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-pencil-square"></i> Attempt Quiz: {{ $quiz->title }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $quiz->title }}</li>
                        </ol>
                    </nav>

                    <!-- Timer -->
                    <div class="alert alert-warning d-flex justify-content-between">
                        <div id="timer" style="font-size: 20px; font-weight: bold; color: red;">Time Left: <span id="time-remaining" class="text-danger"></span></div>
                        <button id="submit-btn" class="btn btn-danger" onclick="submitQuiz()">Submit Quiz</button>
                    </div>

                    <!-- Quiz Questions -->
                    <div class="bg-white mt-4 p-3 border shadow-sm">
                        <form id="quiz-form">
                            @csrf
                            <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                            <input type="hidden" id="attempt_id" name="attempt_id" value="{{ $attempt->id }}">
                            <input type="hidden" id="class_id" name="class_id" value="{{ $quiz->class_id }}">
                            <input type="hidden" id="section_id" name="section_id" value="{{ $quiz->section_id }}">
                            <input type="hidden" id="semester_id" name="semester_id" value="{{ $quiz->semester_id }}">
                            <input type="hidden" id="session_id" name="session_id" value="{{ $quiz->session_id }}">

                            @foreach($quiz->questions as $index => $question)
                            <div class="question-container" data-question="{{ $index }}" style="display: {{ $index === 0 ? 'block' : 'none' }};">
                                <h5>Q{{ $index + 1 }}: {{ $question->question_text }}</h5>

                                @if ($question->type === 'single_choice')
                                    @foreach($question->options as $option)
                                        <div>
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" onchange="saveAnswer({{ $question->id }},{{ $option->id }},'{{ $option->option_text }}')">
                                            {{ $option->option_text }}
                                        </div>
                                    @endforeach
                                @elseif ($question->type === 'multiple_choice')
                                    @foreach($question->options as $option)
                                        <div>
                                            <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}" onchange="saveAnswer({{ $question->id }},{{ $option->id }},'{{ addslashes($option->option_text) }}')">
                                            {{ $option->option_text }}
                                        </div>
                                    @endforeach
                                @else
                                    <textarea name="answers[{{ $question->id }}]" rows="3" class="form-control" onchange="saveAnswer({{ $question->id }}, null, this.value)"></textarea>
                                @endif
                            </div>
                            @endforeach
                        </form>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-secondary" id="prev-btn" onclick="prevQuestion()" disabled>Previous</button>
                        <button class="btn btn-primary" id="next-btn" onclick="nextQuestion()">Next</button>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
<script>
    let totalTime = {{$quiz->duration * 60}}; // Duration in seconds
    let startTime = new Date("{{ \Carbon\Carbon::parse($attempt->started_at)->toIso8601String() }}").getTime();
    let endTime = startTime + totalTime  * 1000;
    // let timeLeft = localStorage.getItem('quiz_time_left') ? parseInt(localStorage.getItem('quiz_time_left')) : totalTime;
    let timerInterval;

    let currentQuestion = 0;
    let questions;

    let quizSubmitted = false; // Track if the quiz is already submitted
    // let timerInterval;

    document.addEventListener("DOMContentLoaded", function () {
        questions = document.querySelectorAll('.question-container'); // Now it loads after the DOM is ready
        questions[currentQuestion].style.display = 'block'; // Ensure the first question is visible
    });

    function updateTimerDisplay(timeLeft) {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById("time-remaining").innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }

    function startTimer() {
           timerInterval = setInterval(() => {
            let now = new Date().getTime();
            let timeLeft = Math.max(0, Math.floor((endTime - now) / 1000));

            updateTimerDisplay(timeLeft);

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                alert("Time is up! Submitting the quiz...");
                submitQuiz();
            }
        }, 1000);
    }
    
    // Start the timer when the page loads
    window.onload = () => {
        startTimer();
    };


    function nextQuestion() {
        if (currentQuestion < questions.length - 1) {
            questions[currentQuestion].style.display = 'none';
            currentQuestion++;
            questions[currentQuestion].style.display = 'block';
        }

        // Enable "Previous" button when moving forward
        document.getElementById('prev-btn').disabled = currentQuestion === 0;
        
        // Disable "Next" button when at the last question
        document.getElementById('next-btn').disabled = currentQuestion === questions.length - 1;
    }

    function prevQuestion() {
        if (currentQuestion > 0) {
            questions[currentQuestion].style.display = 'none';
            currentQuestion--;
            questions[currentQuestion].style.display = 'block';
        }

        // Disable "Previous" button when at the first question
        document.getElementById('prev-btn').disabled = currentQuestion === 0;

        // Enable "Next" button when moving backward
        document.getElementById('next-btn').disabled = false;
    }

    function saveAnswer(questionId, optionId, answerValue) {
        const attemptId = document.getElementById('attempt_id').value;
        const classId = document.getElementById('class_id').value;
        const sectionId = document.getElementById('section_id').value;
        const semesterId = document.getElementById('semester_id').value;
        const sessionId = document.getElementById('session_id').value;
        
        fetch('/student-quizzes/save-answer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                attempt_id: attemptId,
                question_id: questionId,
                answer: answerValue,
                option_id: optionId, 
                class_id: classId,
                section_id: sectionId,
                semester_id: semesterId,
                session_id: sessionId
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200) {
                console.log('Answer saved successfully:', body.message);
            } else {
                console.error(`Failed to save answer (Status ${status}):`, body);
            }
        })
        .catch(err => console.error('Error saving answer:', err));
    }


    function submitQuiz() {
        if (quizSubmitted) return;
        quizSubmitted = true;

        if (!confirm("Are you sure you want to submit the quiz?")) {
            quizSubmitted = false;
            return;
        }

        clearInterval(timerInterval);
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Submitting...';

        const attemptInput = document.getElementById('attempt_id');

        if (!attemptInput || !attemptInput.value) {
            alert("Attempt ID is missing. Please refresh and try again.");
            quizSubmitted = false;
            submitBtn.disabled = false;
            submitBtn.innerText = 'Submit Quiz';
            return;
        }

        const getFieldValue = (id) => document.getElementById(id) ? document.getElementById(id).value : null;
        console.log(getFieldValue('class_id'), getFieldValue('section_id'), getFieldValue('semester_id'), getFieldValue('session_id'));
        
        const requestBody = {
            attempt_id: attemptInput.value,
            class_id: getFieldValue('class_id'),
            section_id: getFieldValue('section_id'),
            semester_id: getFieldValue('semester_id'),
            session_id: getFieldValue('session_id')
        };

        fetch('{{ route('quiz.submit') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert('Quiz submitted successfully! Your score: ' + (data.score ?? 'Unknown'));
                window.location.href = "{{ route('quiz.results', ['attempt_id' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', attemptInput.value);
            } else {
                throw new Error('Unexpected response format');
            }
        })
        .catch(err => {
            console.error('Error submitting quiz:', err);
            alert('Submission failed: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.innerText = 'Submit Quiz';
            quizSubmitted = false;
        });
    }

    // Prevent Copy-Paste
    document.addEventListener('copy', (e) => e.preventDefault());
    document.addEventListener('paste', (e) => e.preventDefault());

</script>
@endsection


