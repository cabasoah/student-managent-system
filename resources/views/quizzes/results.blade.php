@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-people"></i> Student Results for "{{ $quiz->title }}"
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">Quizzes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Results</li>
                        </ol>
                    </nav>

                    <div class="bg-white mt-4 p-3 border shadow-sm">
                        <a href="{{ route('admin.quiz.results.export', $quiz->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-download"></i> Export Results
                        </a>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col"> Number </th>
                                        <th scope="col">Student Name</th>
                                        <th scope="col">Total Marks</th>
                                        <th scope="col">Grade</th>
                                        <th scope="col">Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($studentResults as $result)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $result['student_name'] ?? 'N/A' }}</td>
                                            <td>{{ $result['score'] ?? 'N/A' }}</td>
                                            <td>{{ getGradeOne($result['score']) }}</td>
                                            <td>{{ getGPA($result['score']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No students have attempted this quiz yet.</td>
                                        </tr>
                                    @endforelse                                
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    {{-- <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Back to Quizzes</a> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
