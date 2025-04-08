@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-person-lines-fill"></i> Quizzes
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Student Quizzes</li>
                        </ol>
                    </nav>
                    <div class="mb-4 mt-4">
                        <div class="bg-white border shadow-sm p-3 mt-4">
                            <div class="table-responsive">
                                <table class="table table-responsive">
                                    <thead>
                                        <tr>
                                            <th>Quiz Title</th>
                                            <th>Date Taken</th>
                                            <th>Score</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quizSummaries as $summary)
                                            <tr>
                                                <td>{{ $summary['quiz_title'] }}</td>
                                                <td>{{ \Carbon\Carbon::parse($summary['date_taken'])->format('d M, Y') }}</td>
                                                <td>{{ $summary['score'] }}/{{ $summary['total_marks'] }}</td>
                                                <td>
                                                    <a href="{{ route('quiz.results', $summary['attempt_id']) }}" class="btn btn-sm btn-primary">Preview</a>
                                                    <a href="{{ route('admin.student.quiz.download', $summary['attempt_id']) }}" class="btn btn-sm btn-secondary">Download PDF</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
