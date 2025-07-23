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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('status')->default('pending'); // Example status field
            $table->decimal('total_amount', 10, 2)->default(0.00); // Example total amount field
            $table->string('payment_method')->nullable(); //
            $table->string('no_of_photos')->nullable(); // Example number of photos field
            $table->string('delivery_date')->nullable(); // Example delivery date field
            $table->string('service_type')->nullable(); // Example order type field
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            $table->string('payment_status')->default('unpaid'); // Example payment status field
            $table->string('txn')->nullable(); // Example transaction ID field
                $table->foreignId('chat_id')->nullable()->constrained()->nullOnDelete();

            // Add any other fields you need for the orders table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
