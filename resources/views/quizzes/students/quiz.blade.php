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
                        <strong>Time Left: <span id="timer">00:00</span></strong>
                        <button class="btn btn-danger" onclick="submitQuiz()">Submit Quiz</button>
                    </div>

                    <!-- Quiz Questions -->
                    <form id="quiz-form">
                        @csrf
                        <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                        <input type="hidden" id="attempt_id" name="attempt_id" value="{{ $attempt->id }}">
                        <input type="hidden" id="class_id" name="class_id" value="{{ $attempt->class_id }}">
                        <input type="hidden" id="section_id" name="section_id" value="{{ $attempt->section_id }}">
                        <input type="hidden" id="semester_id" name="semester_id" value="{{ $attempt->semester_id }}">
                        <input type="hidden" id="session_id" name="session_id" value="{{ $attempt->session_id }}">

                        @foreach($quiz->questions as $index => $question)
                        <div class="question-container" data-question="{{ $index }}" style="display: {{ $index === 0 ? 'block' : 'none' }};">
                            <h5>Q{{ $index + 1 }}: {{ $question->question_text }}</h5>

                            @if ($question->type === 'single_choice')
                                @foreach($question->options as $option)
                                    <div>
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" onchange="saveAnswer({{ $question->id }}, {{ $option->id }})">
                                        {{ $option->option_text }}
                                    </div>
                                @endforeach
                            @elseif ($question->type === 'multiple_choice')
                                @foreach($question->options as $option)
                                    <div>
                                        <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}" onchange="saveAnswer({{ $question->id }}, {{ $option->id }})">
                                        {{ $option->option_text }}
                                    </div>
                                @endforeach
                            @else
                                <textarea name="answers[{{ $question->id }}]" rows="3" class="form-control" oninput="saveAnswer({{ $question->id }}, this.value)"></textarea>
                            @endif
                        </div>
                        @endforeach
                    </form>

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

@endsection

@section('scripts')
<script>
    let timeLeft = 10 * 60; // Set quiz duration in seconds (e.g., 10 minutes)
    let interval;
    let currentQuestion = 0;
    const questions = document.querySelectorAll('.question-container');

    function startTimer() {
        interval = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(interval);
                alert('Time is up! Submitting quiz...');
                submitQuiz();
                return;
            }
            document.getElementById('timer').textContent = new Date(timeLeft * 1000).toISOString().substr(14, 5);
            timeLeft--;
        }, 1000);
    }

    function saveAnswer(questionId, answerValue, optionId = null) {
        const attemptId = document.getElementById('attempt_id').value; // Hidden input field storing attempt ID
        const classId = document.getElementById('class_id').value; // Hidden input for class
        const sectionId = document.getElementById('section_id').value; // Hidden input for section
        const semesterId = document.getElementById('semester_id').value; // Hidden input for semester
        const sessionId = document.getElementById('session_id').value; // Hidden input for session

        fetch('{{ route('quiz.save.answer') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
        }).catch(err => console.error('Error saving answer:', err));
    }


    function prevQuestion() {
        if (currentQuestion > 0) {
            questions[currentQuestion].style.display = 'none';
            currentQuestion--;
            questions[currentQuestion].style.display = 'block';
            document.getElementById('next-btn').disabled = false;
        }
        if (currentQuestion === 0) {
            document.getElementById('prev-btn').disabled = true;
        }
    }

    function nextQuestion() {
        if (currentQuestion < questions.length - 1) {
            questions[currentQuestion].style.display = 'none';
            currentQuestion++;
            questions[currentQuestion].style.display = 'block';
            document.getElementById('prev-btn').disabled = false;
        }
        if (currentQuestion === questions.length - 1) {
            document.getElementById('next-btn').disabled = true;
        }
    }

    function submitQuiz() {
        if (!confirm("Are you sure you want to submit your answers?")) {
            return; // Exit function if user cancels submission
        }

        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true; // Disable the button to prevent multiple submissions
        submitBtn.innerText = 'Submitting...'; 

        const attemptId = document.getElementById('attempt_id').value;
        const classId = document.getElementById('class_id').value;
        const sectionId = document.getElementById('section_id').value;
        const semesterId = document.getElementById('semester_id').value;
        const sessionId = document.getElementById('session_id').value;

        fetch('{{ route('quiz.submit') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                attempt_id: attemptId,
                class_id: classId,
                section_id: sectionId,
                semester_id: semesterId,
                session_id: sessionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Quiz submitted successfully') {
                alert('Quiz submitted successfully! Your score: ' + data.score);
                window.location.href = "{{ route('quiz.results', ['attempt_id' => '']) }}" + document.getElementById('attempt_id').value;
            } else {
                alert('Error submitting quiz. Please try again.');
                submitBtn.disabled = false; // Re-enable button if submission fails
                submitBtn.innerText = 'Submit Quiz';
            }
        })
        .catch(err => {
            console.error('Error submitting quiz:', err);
            alert('Something went wrong. Please try again.');
            submitBtn.disabled = false; // Re-enable button
            submitBtn.innerText = 'Submit Quiz';
        });
    }


    // Prevent Copy-Paste
    document.addEventListener('copy', (e) => e.preventDefault());
    document.addEventListener('paste', (e) => e.preventDefault());

    startTimer();
</script>
@endsection
