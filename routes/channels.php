<?php

use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('class-chat.{class_id}.{section_id}', function ($user, $class_id, $section_id) {
    Log::info('🔥 Checking class-chat channel authorization:', [
        'user_id' => $user->id,
        'class_id' => $class_id,
        'section_id' => $section_id,
    ]);
    $user = User::where('id', $user->id)->where('role', 'student')->first();
    if ($user) {
        $student_section = Section::where('class_id', $class_id)->where('id', $section_id)->first();
        if ($student_section) {
            return true;
        }
    }
    return false;
});

