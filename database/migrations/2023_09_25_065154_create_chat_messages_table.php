<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->text('images')->nullable();
            $table->text('videos')->nullable();
            $table->text('documents')->nullable();
            $table->text('audios')->nullable();
            $table->enum('locations', [0, 1])->default(0)->nullable();
            $table->text('longitude')->nullable();
            $table->text('latitude')->nullable();
            $table->text('delete_time')->nullable();
            $table->text('contact_name')->nullable();
            $table->text('contact_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
