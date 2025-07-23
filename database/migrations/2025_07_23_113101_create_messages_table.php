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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('type', ['text', 'image', 'file', 'voice', 'order'])->default('text');

            $table->text('message')->nullable();
            $table->string('file')->nullable();        // image/file/voice URL
            $table->integer('duration')->nullable();    // for voice messages
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->boolean('is_forwarded')->default(false);
            $table->unsignedBigInteger('original_id')->nullable(); // original message ID

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
