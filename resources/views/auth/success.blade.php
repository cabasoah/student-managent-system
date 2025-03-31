@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow mt-5">
                <div class="card-body p-5">
                    <!-- Success Icon -->
                    <div class="text-center mb-4">
                        <div class="success-checkmark">
                            <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" fill="#28a745" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                        </div>
                        <h2 class="h3 font-weight-bold text-success mt-3">Registration Successful!</h2>
                    </div>

                    <!-- Message -->
                    <div class="text-center mb-4">
                        <p class="lead text-muted">
                            Your account has been successfully created using the invitation.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-3">    
                        <a href="{{ route('login') }}" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i> Proceed to Login
                        </a>
                        
                        <a href="/" 
                           class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-home me-2"></i> Return to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Success animation */
    .success-checkmark {
        animation: checkmark 0.5s ease-in-out;
    }
    
    @keyframes checkmark {
        0% { transform: scale(0); opacity: 0; }
        80% { transform: scale(1.2); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endsection