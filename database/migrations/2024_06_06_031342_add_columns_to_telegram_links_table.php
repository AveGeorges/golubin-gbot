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
        Schema::table('telegram_links', function (Blueprint $table) {
            $table->bigInteger('chat_id')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->bigInteger('last_message_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_links', function (Blueprint $table) {
            $table->dropColumn('chat_id');
            $table->dropColumn('last_check_at');
            $table->dropColumn('last_message_id');
        });
    }
};
