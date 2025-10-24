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
        Schema::create('questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'select', 'toggle', 'radioGroup', 'textarea'
            $table->string('label')->nullable(); // Question label
            $table->json('options')->nullable(); // Array of options for select/radio
            $table->string('state_key'); // e.g., 'selectedFace', 'maintainSkinTone'
            $table->integer('order')->default(0); // Order within the category
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_questions');
    }
};
