<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key_hash');
            $table->string('key_prefix', 8)->nullable(); // последние 4 символа для отображения
            $table->timestamp('expires_at')->nullable();
            $table->boolean('disabled')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_keys');
    }
};
