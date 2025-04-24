<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class ClassChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message->load('sender');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        \Log::info('Broadcasting to: class-chat.' . $this->message->class_id . '.' . $this->message->section_id);
        return new PrivateChannel('class-chat.' . $this->message->class_id . '.' . $this->message->section_id);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message
        ];
        // return [
        //     'message' => [
        //         'id' => $this->message->id,
        //         'message' => $this->message->message,
        //         'sender_id' => $this->message->sender_id,
        //         'sender_name' => $this->message->sender->name,
        //         'created_at' => $this->message->created_at->toDateTimeString(),
        //         'class_id' => $this->message->class_id,
        //         'section_id' => $this->message->section_id,
        //     ]
        // ];
    }
}
