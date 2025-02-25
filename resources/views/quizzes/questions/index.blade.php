@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu') <!-- Left Sidebar -->

        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-question-circle"></i> Quiz Questions - {{ $quiz->title }}
                    </h1>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">Quizzes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Questions</li>
                        </ol>
                    </nav>

                    <div class="mb-4">
                        <a href="{{ route('admin.quizzes.questions.create', $quiz->id) }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Add Question
                        </a>
                        <a href="{{ route('admin.questions.bulk-upload-form', $quiz->id) }}" class="btn btn-secondary">
                            <i class="bi bi-upload"></i> Bulk Upload
                        </a>
                    </div>

                    <div class="bg-white p-3 border shadow-sm">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quiz->questions as $question)
                                <tr>
                                    <td>{{ $question->question_text }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $question->type)) }}</td>
                                    <td>
                                        <a href="{{ route('admin.quizzes.questions.edit', [$quiz->id, $question->id]) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.quizzes.questions.destroy', [$quiz->id, $question->id]) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this question?');">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @include('layouts.footer') <!-- Footer -->
        </div>
    </div>
</div>

@endsection
