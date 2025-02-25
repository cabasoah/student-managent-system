@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-file-text"></i> Quizzes
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Quizzes</li>
                        </ol>
                    </nav>

                    <h6>Filter list by:</h6>
                    <div class="mb-4 mt-4">
                        {{-- <form action="{{ route('admin.quizzes.index') }}" method="GET">
                            <div class="row">
                                <div class="col-3">
                                    <select class="form-select" name="class_id">
                                        <option value="">All Classes</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->class_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <select class="form-select" name="semester_id">
                                        <option value="">All Semesters</option>
                                        @foreach ($semesters as $semester)
                                            <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                                                {{ $semester->semester_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-counterclockwise"></i> Load List</button>
                                </div>
                            </div>
                        </form> --}}

                        <div class="bg-white mt-4 p-3 border shadow-sm">
                            <a href="{{ route('admin.quizzes.create') }}" class="btn btn-success mb-3">+ Add New Quiz</a>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Title</th>
                                        <th scope="col">Course</th>
                                        <th scope="col">Created at</th>
                                        <th scope="col">Visibility</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($quizzes as $quiz)
                                        <tr>
                                            <td>{{ $quiz->title }}</td>
                                            <td>{{ $quiz->course->course_name }}</td>
                                            <td>{{ $quiz->created_at }}</td>
                                            <td>
                                                <button class="btn btn-sm {{ $quiz->is_visible_to_student ? 'btn-success' : 'btn-secondary' }}"
                                                    onclick="toggleVisibility({{ $quiz->id }}, this)">
                                                    {{ $quiz->is_visible_to_student ? 'Visible' : 'Hidden' }}
                                                </button>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pen"></i> Edit</a>
                                                    <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Manage Questions</a>
                                                    <form action="{{ route('admin.quizzes.destroy', $quiz->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this quiz?');"><i class="bi bi-trash"></i> Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
<script>
    function toggleVisibility(quizId, button) {
        fetch(`/admin/quizzes/${quizId}/toggle-visibility`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.classList.toggle('btn-success', data.is_visible);
                button.classList.toggle('btn-secondary', !data.is_visible);
                button.textContent = data.is_visible ? 'Visible' : 'Hidden';
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
@endsection
