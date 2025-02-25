@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-file-text"></i> {{ isset($quiz) ? 'Edit Quiz' : 'Create New Quiz' }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">Quizzes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ isset($quiz) ? 'Edit' : 'Create' }}</li>
                        </ol>
                    </nav>

                    <div class="bg-white mt-4 p-3 border shadow-sm">
                        <form action="{{ isset($quiz) ? route('admin.quizzes.update', $quiz->id) : route('admin.quizzes.store') }}" method="POST">
                            @csrf
                            @isset($quiz) @method('PUT') @endisset

                            <div class="mb-3">
                                <label>Quiz Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $quiz->title ?? '') }}" required>
                            </div>

                            <div class="mb-3">
                                <label>Description</label>
                                <textarea name="description" id="description" class="form-control" required cols="5" rows="5">{{ old('title', $quiz->description ?? '') }}</textarea>
                                {{-- <input type="text" name="title" class="form-control" value="{{ old('title', $quiz->title ?? '') }}" required> --}}
                            </div>

                            <div class="mb-3">
                                <label>Course</label>
                                <select name="course_id" class="form-select" required>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ isset($quiz) && $quiz->course_id == $course->id ? 'selected' : '' }}>
                                            {{ $course->course_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="duration" class="form-label">Quiz Duration (Minutes)</label>
                                <input type="number" name="duration" id="duration" class="form-control" value="{{ old('duration', $quiz->duration ?? '') }}" min="1">
                            </div>

                            <button type="submit" class="btn btn-success">Save Quiz</button>
                            <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
