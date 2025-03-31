<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lecturer_invites', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->foreignId('inviter_id')->constrained('users');
            $table->string('email')->nullable(); // Optional: for tracking invited email
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->index('token'); // For faster lookups
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lecturer_invites');
    }
};
