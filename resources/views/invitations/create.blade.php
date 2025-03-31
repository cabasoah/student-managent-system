@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-md-10">
            <div class="card mt-5">
                <div class="card-header">Generate Student Invitation</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('invitations.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="course_id" class="form-label">For Course</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Specific Student Email (optional)</label>
                            <input type="email" class="form-control" id="email" name="email">
                            <div class="form-text">Leave blank for open invitation</div>
                        </div>

                        <div class="mb-3">
                            <label for="expiry_days" class="form-label">Expires After (days)</label>
                            <input type="number" class="form-control" id="expiry_days" name="expiry_days" 
                                   min="1" max="30" value="7" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Generate Invitation Link</button>
                    </form>

                    @if(session('invitation_url'))
                        <div class="mt-4">
                            <h5>Invitation Link:</h5>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="inviteUrl" 
                                       value="{{ session('invitation_url') }}" readonly>
                                <button class="btn btn-outline-secondary" onclick="copyToClipboard()">
                                    Copy
                                </button>
                            </div>
                            <div class="alert alert-info">
                                Share this link with your students. It will expire in {{ $request->expiry_days ?? 7 }} days.
                            </div>
                        </div>

                        <script>
                            function copyToClipboard() {
                                const copyText = document.getElementById("inviteUrl");
                                copyText.select();
                                copyText.setSelectionRange(0, 99999);
                                document.execCommand("copy");
                                alert("Copied to clipboard!");
                            }
                        </script>
                    @endif
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection