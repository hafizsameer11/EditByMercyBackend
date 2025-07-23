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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('service_type'); // e.g., "editing", "retouching"


            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('chat_id')->nullable()->constrained('chats')->nullOnDelete();
            // $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
