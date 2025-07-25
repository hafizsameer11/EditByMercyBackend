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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Main Questionnaire');
            $table->timestamps();
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('form_id')->constrained()->onDelete('cascade')->after('id');
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedInteger('form_id')->nullable()->after('id');
        });
  
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
