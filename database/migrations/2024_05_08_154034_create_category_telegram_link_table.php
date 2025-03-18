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
        Schema::create('category_telegram_link', function (Blueprint $table) {
            $table->foreignid('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignUuid('telegram_link_id')
                ->references('id')
                ->on('telegram_links')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->primary(['category_id', 'telegram_link_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_telegram_link');
    }
};
