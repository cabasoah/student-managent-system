@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-list-task"></i> Available Quizzes
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Quizzes</li>
                        </ol>
                    </nav>
                    
                    <div class="mb-4 p-3 bg-white border shadow-sm">
                        <a href="{{ route('student.quizzes.export', ['course_id' => request('course_id')]) }}" class="btn btn-primary mb-3">
                            <i class="bi bi-download"></i> Export My Quizzes
                        </a>
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Quiz Title</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Duration</th>
                                    <th scope="col">Earned Marks</th>
                                    <th scope="col">Total Marks</th>
                                    {{-- <th scope="col">Grade</th> --}}
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quizzes as $quiz)
                                <tr>
                                    <td>{{ $quiz->title }}</td>
                                    <td>{{ $quiz->description }}</td>
                                    <td>{{ $quiz->duration }} mins</td>
                                    <td>
                                        @if (isset($attemptedQuizzes[$quiz->id]))
                                            {{ $attemptedQuizzes[$quiz->id]->score }}
                                        @else
                                            <span class="text-muted">Not Attempted</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $quiz->total_marks }}
                                    </td>
                                    {{-- <td class="fw-bold">
                                        @if (isset($attemptedQuizzes[$quiz->id]))
                                            {{ getGrade($attemptedQuizzes[$quiz->id]->score,$quiz->total_marks) }}
                                        @else
                                            <span class="text-muted">Not Attempted</span>
                                        @endif
                                    </td> --}}
                                    <td>
                                        @if (!isset($attemptedQuizzes[$quiz->id]))
                                            <a href="{{ route('quiz.instructions', ['quiz_id' => $quiz->id]) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-play-circle"></i> Start Quiz
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="bi bi-check-circle"></i> Attempted
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            
                        </table>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

@endsection