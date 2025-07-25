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
        Schema::create('questionnaire_assignments', function (Blueprint $table) {
         $table->id();

            $table->foreignId('form_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('status', ['assigned', 'in_progress', 'completed', 'closed'])->default('assigned');

            $table->unsignedInteger('completed_sections')->default(0);
            $table->unsignedInteger('total_sections')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_assignments');
    }
};
