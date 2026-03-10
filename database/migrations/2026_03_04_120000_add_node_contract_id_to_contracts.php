<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ID контракта на ноде GND (таблица contracts). Нужен для вызова call/send по by-id.
     * После синхронизации с нодой или деплоя заполняется из ответа ноды.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('node_contract_id')->nullable()->after('address')
                ->comment('ID контракта в БД ноды (для call/send по by-id)');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('node_contract_id');
        });
    }
};
