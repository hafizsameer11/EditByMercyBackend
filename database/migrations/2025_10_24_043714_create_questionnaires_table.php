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
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g., "Face", "Skin", "Change in body size"
            $table->string('icon')->nullable(); // Ionicon name, e.g., "happy-outline"
            $table->string('color')->default('#992C55');
            $table->text('description')->nullable(); // e.g., "Select one or multiple options"
            $table->integer('order')->default(0); // 1, 2, 3 for sorting
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};
