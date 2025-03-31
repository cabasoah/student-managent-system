@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="card mt-5">
                <div class="card-header">Generate Lecturer Registration  Invitation</div>

                <div class="card-body">
                    <form id="inviteForm">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="email">Lecturer Email (optional)</label>
                            <input type="email" class="form-control" id="email" name="email">
                            <small class="text-muted">For tracking purposes only</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Generate Invite Link</button>
                    </form>

                    <div id="result" class="mt-4" style="display: none;">
                        <h5>Invitation Link:</h5>
                        <div class="input-group mb-3">
                            <input type="text" id="inviteUrl" class="form-control" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard()">Copy</button>
                        </div>
                        <p class="text-muted">Expires in 7 days</p>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
<script>
    document.getElementById('inviteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch('{{ route("invite.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                email: document.getElementById('email').value
            })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('inviteUrl').value = data.url;
            document.getElementById('result').style.display = 'block';
        });
    });
    
    function copyToClipboard() {
        const copyText = document.getElementById('inviteUrl');
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand('copy');
        alert('Copied to clipboard!');
    }
</script>
@endsection
