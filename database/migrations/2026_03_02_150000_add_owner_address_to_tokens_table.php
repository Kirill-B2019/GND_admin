<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->string('owner_address', 255)->nullable()->after('token_address')
                ->comment('Адрес владельца (кошелёк выгрузки); при системном токене — gndself_address');
        });
    }

    public function down(): void
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->dropColumn('owner_address');
        });
    }
};
