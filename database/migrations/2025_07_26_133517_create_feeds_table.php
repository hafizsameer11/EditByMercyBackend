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
        // Schema::create('feeds', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
        //     $table->foreignId('category_id')->constrained('feed_categories')->onDelete('set null')->nullable();
        //     $table->string('caption')->nullable();
        //     $table->string('before_image');
        //     $table->string('after_image');
        //     $table->unsignedInteger('likes_count')->default(0); // optimization
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
