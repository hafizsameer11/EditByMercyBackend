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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('reply_preview')->nullable()->after('message');
            $table->unsignedBigInteger('reply_to_id')->nullable()->after('reply_preview');

            // index + fk (set null on delete to avoid cascades blowing history)
            $table->index('reply_to_id', 'messages_reply_to_id_idx');
            $table->foreign('reply_to_id', 'messages_reply_to_id_fk')
                ->references('id')->on('messages')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // drop FK and column in reverse order
            $table->dropForeign('messages_reply_to_id_fk');
            $table->dropIndex('messages_reply_to_id_idx');
            $table->dropColumn(['reply_preview', 'reply_to_id']);
        });
    }

};
