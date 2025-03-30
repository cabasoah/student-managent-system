@extends('layouts.app')

@push('styles')
<style>
    .question-nav-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
    }
    
    .question-nav-btn.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }
    
    .question-nav-btn.answered {
        background-color: #198754;
        color: white;
        border-color: #198754;
    }
    
    .option-card {
        transition: all 0.2s ease;
        cursor: pointer;
        border: 1px solid #dee2e6;
    }
    
    .option-card:hover {
        background-color: #f8f9fa;
        border-color: #adb5bd;
    }
    
    .option-card.selected {
        background-color: #e9f2ff;
        border-color: #0d6efd;
    }
    
    #timer.warning {
        animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    .btn-unanswered {
        background-color: #343a40 !important; /* Dark Gray */
        color: white !important;
    }
    .btn-progress {
        background-color: #007bff !important; /* Bootstrap Blue */
        color: white !important;
    }

    .btn-answered {
        background-color: #28a745 !important; /* Bootstrap Green */
        color: white !important;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Left Menu -->
        <div class="col-md-3 mb-4">
            {{-- @include('layouts.left-menu') --}}
            
            <!-- Question Navigator -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Question Navigator</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        @foreach($quiz->questions as $index => $q)
                            <button 
                                type="button" 
                                class="btn btn-sm question-nav-btn" 
                                data-question="{{ $index }}"
                                id="nav-btn-{{ $index }}">
                                {{ $index + 1 }}
                            </button>
                        @endforeach
                        <button id="submit-btn" class="btn btn-danger mt-2" onclick="submitQuiz()">
                            <i class="bi bi-send me-1"></i> Submit Quiz
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-pencil-square fs-3 me-2 text-dark"></i>
                <h1 class="h2 mb-0">Attempt Quiz: {{ $quiz->title }}</h1>
            </div>
            
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted"> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $quiz->title }}</li>
                </ol>
            </nav>

            <!-- Progress Bar -->
            <div class="progress mb-3" style="height: 8px;">
                <div class="progress-bar bg-success" id="quiz-progress" role="progressbar" style="width: 0%"></div>
            </div>

            <!-- Timer -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-clock fs-4 me-2 text-danger"></i>
                        <div id="timer" class="fs-5 fw-bold text-danger">Time Left: <span id="time-remaining"></span></div>
                    </div>
                    <button id="submit-btn" class="btn btn-danger" onclick="submitQuiz()">
                        <i class="bi bi-send me-1"></i> Submit Quiz
                    </button>
                </div>
            </div>

            <!-- Quiz Questions -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <form id="quiz-form" method="POST" action="{{ route('quiz.submit') }}">
                        @csrf
                        <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                        <input type="hidden" id="attempt_id" name="attempt_id" value="{{ $attempt->id }}">
                        <input type="hidden" id="class_id" name="class_id" value="{{ $quiz->class_id }}">
                        <input type="hidden" id="section_id" name="section_id" value="{{ $quiz->section_id }}">
                        <input type="hidden" id="semester_id" name="semester_id" value="{{ $quiz->semester_id }}">
                        <input type="hidden" id="session_id" name="session_id" value="{{ $quiz->session_id }}">
                        <input type="hidden" id="time_spent" name="time_spent" value="0">

                        @foreach($quiz->questions as $index => $question)
                        <div class="question-container" data-question="{{ $index }}" style="display: {{ $index === 0 ? 'block' : 'none' }};">
                            <h4 class="mb-4">Q{{ $index + 1 }}: {{ $question->question_text }}</h4>

                            @if ($question->type === 'single_choice')
                                <div class="mb-3">
                                    @foreach($question->options as $option)
                                        <div class="card mb-2 option-card">
                                            <div class="card-body py-2">
                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input" 
                                                        type="radio" 
                                                        name="answers[{{ $question->id }}]" 
                                                        id="option-{{ $option->id }}" 
                                                        value="{{ $option->id }}" 
                                                        onchange="saveAnswer({{ $question->id }}, {{ $option->id }}, '{{ addslashes($option->option_text) }}')">
                                                    <label class="form-check-label w-100" for="option-{{ $option->id }}">
                                                        {{ $option->option_text }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($question->type === 'multiple_choice')
                                <div class="mb-3">
                                    @foreach($question->options as $option)
                                        <div class="card mb-2 option-card">
                                            <div class="card-body py-2">
                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input" 
                                                        type="checkbox" 
                                                        name="answers[{{ $question->id }}][]" 
                                                        id="option-{{ $option->id }}" 
                                                        value="{{ $option->id }}" 
                                                        onchange="saveAnswer({{ $question->id }}, {{ $option->id }}, '{{ addslashes($option->option_text) }}')">
                                                    <label class="form-check-label w-100" for="option-{{ $option->id }}">
                                                        {{ $option->option_text }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mb-3">
                                    <textarea 
                                        name="answers[{{ $question->id }}]" 
                                        class="form-control" 
                                        rows="5" 
                                        placeholder="Type your answer here..."
                                        onchange="saveAnswer({{ $question->id }}, null, this.value)"></textarea>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </form>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="d-flex justify-content-between mb-4">
                <button class="btn btn-outline-secondary" id="prev-btn" onclick="prevQuestion()" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left mr-2 h-4 w-4"><path d="m15 18-6-6 6-6"></path></svg> Previous
                </button>
                
                <div class="d-flex align-items-center">
                    <span id="question-counter" class="text-muted">Question 1 of {{ count($quiz->questions) }}</span>
                </div>
                
                <button class="btn btn-light btn-outline-secondary" id="next-btn" onclick="nextQuestion()">
                    Next <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right ml-2 h-4 w-4"><path d="m9 18 6-6-6-6"></path></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="submitConfirmModal" tabindex="-1" aria-labelledby="submitConfirmModalLabel" inert>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submitConfirmModalLabel">Confirm Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit for final marking?</p>
                <div id="unanswered-warning" class="alert alert-warning d-none">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    You have unanswered questions. Are you sure you want to proceed?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-success" onclick="submitQuiz()">Yes</button>
            </div>
        </div>
    </div>
</div>

<script>
    let totalTime = {{$quiz->duration * 60}}; // Duration in seconds
    let startTime = new Date("{{ \Carbon\Carbon::parse($attempt->started_at)->toIso8601String() }}").getTime();
    let endTime = startTime + totalTime * 1000;
    let timerInterval;

    let currentQuestion = 0;
    const totalQuestions = {{ count($quiz->questions) }};
    let questions;

    let quizSubmitted = false; // Track if the quiz is already submitted

    document.addEventListener("DOMContentLoaded", function () {
        questions = document.querySelectorAll('.question-container'); 
        questions[currentQuestion].style.display = 'block';
        updateProgressBar();
        updateNavigator();

        // Add event listeners to navigation buttons
        document.querySelectorAll('.question-nav-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const questionIndex = parseInt(this.getAttribute('data-question'));
                changeQuestion(questionIndex);
            });
        });

        // Track changes in answer inputs and update the navigator
        document.querySelectorAll('.question-container input[type="radio"], .question-container input[type="checkbox"], .question-container textarea, .question-container input[type="text"]').forEach(input => {
            input.addEventListener('change', function () {
                updateNavigator();
            });
        });
    });

    function updateTimerDisplay(timeLeft) {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById("time-remaining").innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }

    function startTimer() {
        let savedTime = localStorage.getItem("remainingTime");
    
        if (savedTime) {
            totalTime = parseInt(savedTime); // Restore remaining seconds
        } else {
            totalTime = {{$quiz->duration * 60}}; 
        }

        endTime = new Date().getTime() + totalTime * 1000; // Set countdown end time

        timerInterval = setInterval(() => {
            let now = new Date().getTime();
            let timeLeft = Math.max(0, Math.floor((endTime - now) / 1000));

            localStorage.setItem("remainingTime", timeLeft); // Store remaining time
            updateTimerDisplay(timeLeft); // Update UI

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                localStorage.removeItem("remainingTime"); // Clear storage when time is up
                submitQuizAuto();
            }
        }, 1000);
    }
    
    // Ensure timer is started when the page loads
    document.addEventListener("DOMContentLoaded", () => {
        startTimer();
    });

    function updateProgressBar() {
        const progressBar = document.getElementById('quiz-progress'); // Corrected ID
        if (progressBar) {
            progressBar.style.width = ((currentQuestion + 1) / totalQuestions) * 100 + '%';
        }
    }

    function updateNavigator() {
    const navButtons = document.querySelectorAll('.question-nav-btn');
    navButtons.forEach((btn, index) => {
        let isAnswered = checkIfAnswered(index);

        // Force remove existing color classes
        btn.classList.remove('btn-secondary', 'btn-success', 'btn-primary');

        // Check current state and apply colors
        if (index === currentQuestion) {
            btn.classList.add('btn-inprogress'); // Blue for active question
        } else if (isAnswered) {
            btn.classList.add('btn-answered'); // Green for answered questions
        } else {
            btn.classList.add('btn-unanswered'); // Gray for unanswered questions
        }
        updateQuestionCounter(); 

    });
}

    function checkIfAnswered(index) {
        const questionContainer = questions[index];

        // Check if any radio or checkbox input is selected
        const checkedInputs = questionContainer.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked');
        
        // Check if any textarea or text input has content
        const textInputs = questionContainer.querySelectorAll('textarea, input[type="text"]');
        const hasText = Array.from(textInputs).some(input => input.value.trim() !== '');

        return checkedInputs.length > 0 || hasText;
    }

    function changeQuestion(index) {
        if (index >= 0 && index < totalQuestions) {
            questions[currentQuestion].style.display = 'none';
            currentQuestion = index;
            questions[currentQuestion].style.display = 'block';
            updateProgressBar();
            updateNavigator();
            
            document.getElementById('prev-btn').disabled = currentQuestion === 0;
            document.getElementById('next-btn').disabled = currentQuestion === questions.length - 1;
        }
    }

    function nextQuestion() {
        if (currentQuestion < questions.length - 1) {
            questions[currentQuestion].style.display = 'none';
            currentQuestion++;
            questions[currentQuestion].style.display = 'block';
            updateProgressBar();
            updateNavigator();
            updateQuestionCounter(); 
        }

        document.getElementById('prev-btn').disabled = currentQuestion === 0;

        const nextBtn = document.getElementById('next-btn');
        if (currentQuestion === questions.length - 1) {
            nextBtn.innerText = "Finished, Submit Quiz";
            nextBtn.onclick = submitQuiz; // Change button behavior to submit quiz
        } else {
            nextBtn.innerText = "Next";
            nextBtn.onclick = nextQuestion; // Ensure it goes to the next question normally
        }
    }

    function prevQuestion() {
        if (currentQuestion > 0) {
            questions[currentQuestion].style.display = 'none';
            currentQuestion--;
            questions[currentQuestion].style.display = 'block';
            updateProgressBar();
            updateNavigator();
            updateQuestionCounter(); 
        }

        // Update button states
        document.getElementById('prev-btn').disabled = currentQuestion === 0;
        
        const nextBtn = document.getElementById('next-btn');
        nextBtn.disabled = false; // Always enable next button when going back
        nextBtn.innerText = "Next"; // Reset to "Next" when not on last question
        nextBtn.onclick = nextQuestion;
    }

    function updateQuestionCounter() {
        const counterElement = document.getElementById('question-counter');
        counterElement.textContent = `Question ${currentQuestion + 1} of ${questions.length}`;
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
        localStorage.removeItem("remainingTime");
        
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

    // when time is up, submit the quiz
    function submitQuizAuto() {
        localStorage.removeItem("remainingTime");
        
        if (quizSubmitted) return;
        quizSubmitted = true;

        // if (!confirm("Are you sure you want to submit the quiz?")) {
        //     quizSubmitted = false;
        //     return;
        // }

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
    document.addEventListener('copy', (e) => e.preventDefault());
    document.addEventListener('paste', (e) => e.preventDefault());
</script>


@endsection


