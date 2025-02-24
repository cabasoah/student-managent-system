@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between mb-3">
        <h3>Manage Quizzes</h3>
        <a href="{{ route('quizes.create') }}" class="btn btn-success">Create New Quiz</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Duration (min)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quizzes as $quiz)
            <tr>
                <td>{{ $quiz->title }}</td>
                <td>{{ $quiz->description }}</td>
                <td>{{ $quiz->duration }}</td>
                <td>
                    <a href="{{ route('quizes.show', $quiz->id) }}" class="btn btn-info btn-sm">Manage Questions</a>
                    <a href="{{ route('quizes.edit', $quiz->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('quizes.destroy', $quiz->id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this quiz?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
