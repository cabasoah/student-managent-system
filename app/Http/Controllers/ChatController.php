<?php

namespace App\Http\Controllers;

use App\Events\ClassChatMessageSent;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Support\Facades\Log;


class ChatController extends Controller
{
  
    public function index(Request $request)
    {
        
        $class_id = $request->query('class_id', 0);
        $section_id = $request->query('section_id', 0);
        $messages = Message::where('class_id', $class_id)
                        ->where('section_id', $section_id)
                        ->latest()
                        ->take(50)
                        ->get()
                        ->reverse();
        
        $class_name = SchoolClass::where('id', $class_id)->value('class_name');
        $section_name = Section::where('id', $section_id)->value('section_name');
      
        return view('chat.index', compact('messages', 'class_name', 'section_name', 'class_id', 'section_id'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }
        
        $request->validate(['message' => 'required|string']);
        $fullname = $user->first_name . ' ' . $user->last_name;
        
        $message = Message::create([
            'sender_id' => $user->id,
            'sender_name' => $fullname,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'message' => $request->message,
        ]);

        // Inside your controller:
        Log::info('🔥 About to broadcast message:', ['message_id' => $message->id]);

        // Fire real-time event
        broadcast(new ClassChatMessageSent($message));
        return response()->json(['status' => 'Message sent!']);
    }

}
