<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token_address')->unique()->comment('Адрес контракта токена в сети');
            $table->string('symbol', 32);
            $table->string('name');
            $table->unsignedTinyInteger('decimals')->default(18);
            $table->string('standard', 32)->nullable();
            $table->string('total_supply', 78)->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path', 512)->nullable()->comment('Путь к загруженному файлу логотипа в storage');
            $table->string('logo_url', 512)->nullable()->comment('Внешняя ссылка на логотип');
            $table->json('metadata_json')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
