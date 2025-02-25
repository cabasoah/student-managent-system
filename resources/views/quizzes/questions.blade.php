@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-file-text"></i> Manage Questions
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">Quizzes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Manage Questions</li>
                        </ol>
                    </nav>

                    <div class="bg-white mt-4 p-3 border shadow-sm">
                        <a href="{{ route('admin.questions.create', $quiz->id) }}" class="btn btn-success mb-3">+ Add New Question</a>

                        <ul class="list-group">
                            @foreach($quiz->questions as $question)
                            <li class="list-group-item">
                                <strong>Q:</strong> {{ $question->question_text }}
                                <a href="{{ route('admin.questions.edit', $question->id) }}" class="btn btn-primary btn-sm float-end">Edit</a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
