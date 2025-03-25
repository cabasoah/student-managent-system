@extends('layouts.app')

@push('styles')
<style>
    .instruction-content {
        line-height: 1.7;
    }
    
    .instruction-content ul {
        padding-left: 1.5rem;
    }
    
    .card {
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <!-- Left Menu -->

        @include('layouts.left-menu')

        <!-- Main Content -->
        <div class="col-lg-10 col-xl-10 px-4 py-3">
            <!-- Mobile Menu Toggle -->
            <div class="d-lg-none mb-3">
                <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                    <i class="bi bi-list me-2"></i> Menu
                </button>
            </div>

            <!-- Mobile Menu Offcanvas -->
            <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Menu</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    @include('layouts.left-menu')
                </div>
            </div>

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-2 text-gray-800">
                        <i class="bi bi-info-circle-fill text-secondary me-2"></i> Quiz Instructions
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('student.quizes.index', ['course_id' => $quiz->course_id])}}" class="text-decoration-none">Quizzes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $quiz->title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Quiz Instructions Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-white py-3">
                    <h2 class="h4 mb-0 text-center">{{ $quiz->title }}</h2>
                </div>
                <div class="card-body p-4">
                    <!-- Quiz Details -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="bi bi-clock fs-1 text-primary mb-2"></i>
                                    <h5 class="card-title">Duration</h5>
                                    <p class="card-text fs-4 fw-bold mb-0">{{ $quiz->duration }} minutes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="bi bi-award fs-1 text-primary mb-2"></i>
                                    <h5 class="card-title">Total Marks</h5>
                                    <p class="card-text fs-4 fw-bold mb-0">{{ $quiz->total_marks }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="bi bi-question-circle fs-1 text-primary mb-2"></i>
                                    <h5 class="card-title">Questions</h5>
                                    <p class="card-text fs-4 fw-bold mb-0">{{ $quiz->questions->count() ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mb-4">
                        <h4 class="border-bottom pb-2 mb-3">Instructions</h4>
                        <div class="instruction-content">
                            {!! $quiz->instructions ?? '<p class="text-muted">No specific instructions provided for this quiz.</p>' !!}
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="alert alert-warning">
                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Important Notes</h5>
                        <ul class="mb-0">
                            <li>Make sure to complete the quiz before the timer runs out.</li>
                            <li>Once you start the quiz, the timer cannot be paused.</li>
                            <li>Ensure you have a stable internet connection before starting.</li>
                            <li>Do not refresh or navigate away from the quiz page.</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center mt-4">
                        <a href="{{ route('quiz.attempt', ['quiz_id' => $quiz->id]) }}" class="btn btn-success btn-lg px-5 me-3">
                            <i class="bi bi-play-circle-fill me-2"></i> Start Quiz
                        </a>
                        <a href="{{route('student.quizes.index', ['course_id' => $quiz->course_id])}}" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="bi bi-arrow-left me-2"></i> Back to Quizzes
                        </a>
                    </div>
                </div>
            </div>

            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection


