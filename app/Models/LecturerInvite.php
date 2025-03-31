<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LecturerInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'inviter_id',
        'email',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function isValid()
    {
        return !$this->used && $this->expires_at->isFuture();
    }
}
