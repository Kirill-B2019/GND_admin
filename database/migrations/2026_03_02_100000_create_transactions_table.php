<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Лог отправленных переводов (кошелёк → кошелёк): токен/монета, сумма, nonce (опционально).
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('from_address', 128)->index();
            $table->string('to_address', 128)->index();
            $table->string('token_address', 128)->nullable()->comment('Адрес контракта токена; null = нативная монета');
            $table->string('coin_symbol', 32)->nullable()->comment('Символ монеты (GND, GANI) если token_address пуст');
            $table->string('amount', 78);
            $table->unsignedBigInteger('nonce')->nullable();
            $table->string('tx_hash', 128)->nullable();
            $table->string('status', 32)->default('pending')->comment('pending, sent, failed');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
