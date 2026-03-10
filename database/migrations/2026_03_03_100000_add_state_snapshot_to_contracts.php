<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->json('state_snapshot')->nullable()->comment('Снимок состояния контракта (name, symbol, total_supply, balances) с ноды');
            $table->timestamp('state_snapshot_at')->nullable()->comment('Время последнего снимка состояния');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['state_snapshot', 'state_snapshot_at']);
        });
    }
};
