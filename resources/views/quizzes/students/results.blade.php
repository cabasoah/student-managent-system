@extends('layouts.app')
@push('styles')
<style>
    /* Custom styles for the circular progress */
    .progress-circle circle {
        transition: stroke-dashoffset 0.5s ease;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }
    
    /* Card styling */
    .card {
        border-radius: 0.5rem;
    }
    
    .card-header {
        border-top-left-radius: 0.5rem !important;
        border-top-right-radius: 0.5rem !important;
    }
    
    /* Accordion styling */
    .accordion-item {
        border-radius: 0.5rem !important;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    
    .accordion-button {
        padding: 1rem 1.25rem;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: rgba(var(--bs-primary-rgb), 0.05);
        box-shadow: none;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(var(--bs-primary-rgb), 0.25);
    }
</style>
@endpush
@section('content')
<div class="container py-4">
    <div class="row justify-content-start">
        @include('layouts.left-menu')

        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <!-- Header -->
                    <h1 class="display-6 mb-2 d-flex align-items-center">
                        <i class="bi bi-clipboard-check text-primary me-2"></i> Quiz Results
                    </h1>

                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Quiz Results</li>
                        </ol>
                    </nav>

                    <!-- Main Content -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h2 class="card-title h4 mb-0">{{ $attempt->quiz->title }}</h2>
                        </div>
                        
                        <div class="card-body">
                          <!-- Score Summary Table -->
                            <div class="card bg-light border-0 mb-5">
                                <div class="card-body py-4">
                                    <h5 class="text-center mb-3">Quiz Summary</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Score (%)</th>
                                                    <th>Earned Marks</th>
                                                    <th>Total Marks</th>
                                                    <th>Correct Answers</th>
                                                    <th>Total Questions</th>
                                                    <th>Grade</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold">{{ $score }}%</td>
                                                    <td>{{ $earnedMarks }}</td>
                                                    <td>{{ $totalMarks }}</td>
                                                    <td>{{ $correctAnswers }}</td>
                                                    <td>{{ $attempt->quiz->questions->count() }}</td>
                                                    <td class="fw-bold">{{ getGrade($earnedMarks, $totalMarks) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Question Review -->
                            <h3 class="mb-4">Question Review</h3>
                            <div class="accordion" id="questionAccordion">
                                @foreach ($questionsWithAnswers as $index => $item)
                                    @php
                                        $isCorrect = isset($item['isCorrect']) ? $item['isCorrect'] : null;
                                        $borderClass = $isCorrect === false ? 'border-danger border-opacity-25' : 
                                                    ($isCorrect === true ? 'border-success border-opacity-25' : '');
                                    @endphp
                                    
                                    <div class="accordion-item mb-3 {{ $borderClass }}">
                                        <h2 class="accordion-header" id="heading{{ $index }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                                                <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                                                    <div>
                                                        <span class="text-muted me-2">Q{{ $index + 1 }}:</span>
                                                        {{ $item['question'] }}
                                                    </div>
                                                    <div class="ms-auto">
                                                        @if($isCorrect === true)
                                                            <i class="bi bi-check-circle-fill text-success"></i>
                                                        @elseif($isCorrect === false)
                                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                                        @elseif($item['questionType'] === 'open_ended')
                                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                                {{ $item['awardedMark'] }}/{{ $item['maxMark'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $index }}" data-bs-parent="#questionAccordion">
                                            <div class="accordion-body">
                                                <div class="mb-3">
                                                    <p class="text-muted small mb-1">Your Answer:</p>
                                                    <p class="{{ $isCorrect === false ? 'text-danger' : '' }}">
                                                        @if($item['questionType'] === 'multiple_choice')
                                                            <!-- Loop through selected options for multiple_choice -->
                                                            @foreach(explode(',', $item['studentAnswer']) as $answer)
                                                                <span class="badge bg-secondary">{{ $answer }}</span>
                                                            @endforeach
                                                        @else
                                                            {{ $item['studentAnswer'] }}
                                                        @endif
                                                        @if(in_array($item['questionType'], ['single_choice', 'multiple_choice']) && !$isCorrect)
                                                            <span class="text-danger ms-2">(Incorrect)</span>
                                                        @endif
                                                    </p>
                                                </div>

                                                <div class="mb-3">
                                                    <p class="text-muted small mb-1">Correct Answer:</p>
                                                    <p class="text-success">
                                                        @if($item['questionType'] === 'multiple_choice')
                                                            <!-- Loop through correct options for multiple_choice -->
                                                            @foreach(explode(',', $item['correctAnswer']) as $correctOption)
                                                                <span class="badge bg-success">{{ $correctOption }}</span>
                                                            @endforeach
                                                        @else
                                                            {{ $item['correctAnswer'] }}
                                                        @endif
                                                    </p>
                                                </div>

                                                @if($item['questionType'] === 'open_ended')
                                                    <div>
                                                        <p class="text-muted small mb-1">Marks Awarded:</p>
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="progress" style="height: 8px; width: 150px;">
                                                                <div class="progress-bar" role="progressbar" 
                                                                    style="width: {{ ($item['awardedMark'] / $item['maxMark']) * 100 }}%;" 
                                                                    aria-valuenow="{{ $item['awardedMark'] }}" 
                                                                    aria-valuemin="0" 
                                                                    aria-valuemax="{{ $item['maxMark'] }}">
                                                                </div>
                                                            </div>
                                                            <span class="small fw-medium">
                                                                {{ $item['awardedMark'] }} / {{ $item['maxMark'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Question Review -->
                            {{-- <h3 class="mb-4">Question Review</h3>
                            <div class="accordion" id="questionAccordion">
                                @foreach ($questionsWithAnswers as $index => $item)
                                    @php
                                        $isCorrect = isset($item['isCorrect']) ? $item['isCorrect'] : null;
                                        $borderClass = $isCorrect === false ? 'border-danger border-opacity-25' : 
                                                      ($isCorrect === true ? 'border-success border-opacity-25' : '');
                                    @endphp
                                    
                                    <div class="accordion-item mb-3 {{ $borderClass }}">
                                        <h2 class="accordion-header" id="heading{{ $index }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                                                <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                                                    <div>
                                                        <span class="text-muted me-2">Q{{ $index + 1 }}:</span>
                                                        {{ $item['question'] }}
                                                    </div>
                                                    <div class="ms-auto">
                                                        @if($isCorrect === true)
                                                            <i class="bi bi-check-circle-fill text-success"></i>
                                                        @elseif($isCorrect === false)
                                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                                        @elseif($item['questionType'] === 'open_ended')
                                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                                {{ $item['awardedMark'] }}/{{ $item['maxMark'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $index }}" data-bs-parent="#questionAccordion">
                                            <div class="accordion-body">
                                                <div class="mb-3">
                                                    <p class="text-muted small mb-1">Your Answer:</p>
                                                    <p class="{{ $isCorrect === false ? 'text-danger' : '' }}">
                                                        {{ $item['studentAnswer'] }}
                                                        @if(in_array($item['questionType'], ['single_choice', 'multiple_choice']) && !$isCorrect)
                                                            <span class="text-danger ms-2">(Incorrect)</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <p class="text-muted small mb-1">Correct Answer:</p>
                                                    <p class="text-success">{{ $item['correctAnswer'] }}</p>
                                                </div>
                                                
                                                @if($item['questionType'] === 'open_ended')
                                                    <div>
                                                        <p class="text-muted small mb-1">Marks Awarded:</p>
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="progress" style="height: 8px; width: 150px;">
                                                                <div class="progress-bar" role="progressbar" 
                                                                    style="width: {{ ($item['awardedMark'] / $item['maxMark']) * 100 }}%;" 
                                                                    aria-valuenow="{{ $item['awardedMark'] }}" 
                                                                    aria-valuemin="0" 
                                                                    aria-valuemax="{{ $item['maxMark'] }}">
                                                                </div>
                                                            </div>
                                                            <span class="small fw-medium">
                                                                {{ $item['awardedMark'] }} / {{ $item['maxMark'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div> --}}
                        </div>
                        
                        <div class="card-footer bg-white text-center py-4">
                            <a href="{{ route('home') }}" class="btn btn-primary">
                                <i class="bi bi-house-door me-1"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection