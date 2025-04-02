@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-person-lines-fill"></i> Lecturer List
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Lecturer List</li>
                        </ol>
                    </nav>
                    <div class="mb-4 p-3 bg-white border shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-responsive">
                                <thead>
                                    <tr>
                                        <th scope="col">Photo</th>
                                        <th scope="col">First Name</th>
                                        <th scope="col">Last Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($teachers as $teacher)
                                    <tr>
                                        <td>
                                            @if (isset($teacher->photo))
                                                <img src="{{asset('/storage'.$teacher->photo)}}" class="rounded" alt="Profile picture" height="30" width="30">
                                            @else
                                                <i class="bi bi-person-square"></i>
                                            @endif
                                        </td>
                                        <td>{{$teacher->first_name}}</td>
                                        <td>{{$teacher->last_name}}</td>
                                        <td>{{$teacher->email}}</td>
                                        <td>{{$teacher->phone}}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <span><a href="{{url('teachers/view/profile/'.$teacher->id)}}" role="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Profile</a></span>
                                                @can('edit users')
                                                <span><a href="{{route('teacher.edit.show', ['id' => $teacher->id])}}" role="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-pen"></i> Edit</a></span>
                                                <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger delete-btn">
                                                        <i class="bi bi-trash2"></i> Delete
                                                    </button>
                                                </form>
                                                @endcan
                                                {{-- <button type="button" class="btn btn-sm btn-primary"><i class="bi bi-trash2"></i> Delete</button> --}}
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
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent immediate submission
            if (confirm('Are you sure you want to delete this teacher?')) {
                form.submit(); // Proceed if confirmed
            }
        });
    });
});
</script>