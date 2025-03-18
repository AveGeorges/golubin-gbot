<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPrivateToTelegramLinksTable extends Migration
{
    public function up()
    {
        Schema::table('telegram_links', function (Blueprint $table) {
            $table->boolean('is_private')->default(false); // Добавляем поле is_private
        });
    }

    public function down()
    {
        Schema::table('telegram_links', function (Blueprint $table) {
            $table->dropColumn('is_private'); // Удаляем поле при откате миграции
        });
    }
}
