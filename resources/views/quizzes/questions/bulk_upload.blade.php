@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu') <!-- Left Sidebar -->

        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-upload"></i> Bulk Upload Questions - {{ $quiz->title }}
                    </h1>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">Quizzes</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.questions', $quiz->id) }}">Questions</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bulk Upload</li>
                        </ol>
                    </nav>

                    <div class="bg-white p-3 border shadow-sm">
                        <form action="{{ route('admin.questions.bulk-upload', $quiz->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">Upload CSV/Excel File</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-cloud-upload"></i> Upload
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @include('layouts.footer') <!-- Footer -->
        </div>
    </div>
</div>
@endsection
