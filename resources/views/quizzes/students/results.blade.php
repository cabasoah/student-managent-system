@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu') <!-- Left menu included -->

        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-clipboard-check"></i> Quiz Results
                    </h1>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Quiz Results</li>
                        </ol>
                    </nav>

                    <div class="bg-white p-4 border shadow-sm">
                        <h2 class="mb-4">Quiz: {{ $attempt->quiz->title }}</h2>

                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <h4>Your Score: <span class="text-primary">{{ $score }}%</span></h4>
                                <p class="text-muted">
                                    Correct Answers: <strong>{{ $correctAnswers }} / {{ $totalQuestions }}</strong>
                                </p>
                            </div>
                        </div>

                        <h3 class="mt-4">Question Review</h3>
                        <div class="card">
                            <div class="card-body">
                                <ul class="list-group">
                                    @foreach ($attempt->quiz->questions as $question)
                                        <li class="list-group-item">
                                            <strong>Q:</strong> {{ $question->question_text }}

                                            @php
                                                $studentAnswer = $attempt->answers->where('question_id', $question->id)->first();
                                                $correctOption = $question->options->where('is_correct', true)->first();
                                            @endphp

                                            <p class="mt-2">
                                                <strong>Your Answer:</strong> 
                                                @if ($studentAnswer)
                                                    {{ $studentAnswer->option ? $studentAnswer->option->option_text : $studentAnswer->answer_text }}
                                                    @if ($studentAnswer->option && !$studentAnswer->option->is_correct)
                                                        <span class="text-danger">(Incorrect)</span>
                                                    @endif
                                                @else
                                                    <span class="text-warning">No answer provided</span>
                                                @endif
                                            </p>

                                            <p><strong>Correct Answer:</strong> 
                                                <span class="text-success">{{ $correctOption->option_text ?? 'N/A' }}</span>
                                            </p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <a href="{{ route('home') }}" class="btn btn-primary"><i class="bi bi-house-door"></i> Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer') <!-- Footer included -->
        </div>
    </div>
</div>
@endsection
