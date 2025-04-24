@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        @include('layouts.left-menu')
        <div class="col-md-10">
            <div class="card shadow border-0 rounded-3 overflow-hidden">
                <!-- Chat Header -->
                <div class="card-header bg-primary bg-gradient text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-semibold">{{$class_name}} - {{$section_name}} Chat</h5>
                    <span class="badge bg-success rounded-pill">Online</span>
                </div>
                
                <!-- Chat Messages -->
                <div class="card-body bg-light p-4" style="height: 400px; overflow-y: auto;" id="chat-messages">
                    @foreach ($messages as $message)
                        @php
                            $isCurrentUser = $message->sender->id === auth()->id();
                            $username = $message->sender_name;
                        @endphp
                        <div class="d-flex mb-3 {{ $isCurrentUser ? 'justify-content-end' : 'justify-content-start' }}">
                            @if(!$isCurrentUser)
                                <div class="me-2">
                                    <div class="avatar bg-primary bg-opacity-25 text-white d-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px;">
                                        <span class="fw-bold">{{ substr($username, 0, 1) }}</span>
                                    </div>
                                </div>
                            @endif
                            
                            <div style="max-width: 75%;">
                                @if(!$isCurrentUser)
                                    <div class="fw-semibold text-dark small mb-1">{{ $username }}</div>
                                @endif
                                
                                <div class="p-3 rounded-3 shadow-sm {{ $isCurrentUser ? 'bg-primary text-white' : 'bg-white' }}">
                                    <p class="mb-1">{{ $message->message }}</p>
                                    <div class="d-flex align-items-center {{ $isCurrentUser ? 'text-white-50' : 'text-muted' }} small">
                                        <span>{{ $message->created_at->format('h:i A') }}</span>
                                        <span class="ms-1">· {{ $message->created_at->diffForHumans(null, true, true) }}</span>
                                        
                                        @if($isCurrentUser)
                                            <i class="bi bi-check-all ms-1"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Chat Input -->
                <div class="card-footer bg-white border-top p-3">
                    {{-- <form method="POST" action="{{ route('chat.store') }}">
                        @csrf
                        <div class="input-group">
                            <input 
                                type="text" 
                                name="message" 
                                class="form-control rounded-pill rounded-end-0 border-end-0 py-2 px-3" 
                                placeholder="Type your message..." 
                                required
                            >
                            <input type="hidden" name="class_id" value="{{ $class_id }}">
                            <input type="hidden" name="section_id" value="{{ $section_id }}">
                            <button class="btn btn-primary rounded-pill rounded-start-0 d-flex align-items-center" type="submit">
                                <span>Send</span>
                                <i class="bi bi-send-fill ms-1"></i>
                            </button>
                        </div>
                    </form> --}}
                    <form id="chat-form">
                        @csrf
                        <div class="input-group">
                            <input 
                                type="text" 
                                name="message" 
                                id="message-input"
                                class="form-control rounded-pill rounded-end-0 border-end-0 py-2 px-3" 
                                placeholder="Type your message..." 
                                required
                            >
                            <input type="hidden" name="class_id" value="{{ $class_id }}">
                            <input type="hidden" name="section_id" value="{{ $section_id }}">
                            <button class="btn btn-primary rounded-pill rounded-start-0 d-flex align-items-center" type="submit">
                                <span>Send</span>
                                <i class="bi bi-send-fill ms-1"></i>
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    // Auto-scroll to bottom of chat on page load
    document.addEventListener('DOMContentLoaded', function() {
        const chatContainer = document.getElementById('chat-messages');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    });
</script>
<script>
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = e.target;
        const messageInput = document.getElementById('message-input');
        
        const class_id = {{ $class_id }};
        const section_id = {{ $section_id }};
        console.log(`class-chat.${class_id}.${section_id}`);

        axios.post("{{ route('chat.store') }}", new FormData(form))
            .then(response => {
                messageInput.value = ''; // Clear input
            })
            .catch(error => {
                console.error('Error sending message:', error);
            });
    });
</script>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
       if (typeof Echo !== 'undefined') {
           const class_id = {{ $class_id }};
           const section_id = {{ $section_id }};
           
           Echo.private(`class-chat.${class_id}.${section_id}`)
               .listen('ClassChatMessageSent', (e) => {
                   console.log('Received message:', e.message);
                   const container = document.getElementById('chat-messages');
                   const message = e.message;

                   // Detect if sender is current user
                   const isCurrentUser = message.sender_id === {{ auth()->id() }};
                   const bubble = document.createElement('div');
                   bubble.classList.add('d-flex', 'mb-3');
                   bubble.classList.add(isCurrentUser ? 'justify-content-end' : 'justify-content-start');

                   bubble.innerHTML = `
                       ${!isCurrentUser ? `
                           <div class="me-2">
                               <div class="avatar bg-primary bg-opacity-25 text-white d-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px;">
                                   <span class="fw-bold">${message.sender_name.charAt(0)}</span>
                               </div>
                           </div>
                       ` : ''}
                       <div style="max-width: 75%;">
                           ${!isCurrentUser ? `<div class="fw-semibold text-dark small mb-1">${message.sender_name}</div>` : ''}
                           <div class="p-3 rounded-3 shadow-sm ${isCurrentUser ? 'bg-primary text-white' : 'bg-white'}">
                               <p class="mb-1">${message.message}</p>
                               <div class="d-flex align-items-center ${isCurrentUser ? 'text-white-50' : 'text-muted'} small">
                                   <span>Just now</span>
                                   ${isCurrentUser ? `<i class="bi bi-check-all ms-1"></i>` : ''}
                               </div>
                           </div>
                       </div>
                   `;

                   container.appendChild(bubble);
                   container.scrollTop = container.scrollHeight;
               });
           } else {
               console.error("Echo is not defined. Check if app.js is loaded.");
           }
       });
</script>
@endpush
