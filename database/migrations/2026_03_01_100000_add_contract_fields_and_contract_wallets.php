<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->longText('source_code')->nullable()->after('description');
            $table->text('bytecode_hex')->nullable()->after('source_code');
            $table->longText('abi_json')->nullable()->after('bytecode_hex');
            $table->string('status', 32)->default('draft')->after('abi_json'); // draft, analyzed, deployed, verified
            $table->string('compiler_version', 64)->nullable()->after('status');
            $table->string('metadata_cid', 128)->nullable()->after('compiler_version');
            $table->string('source_cid', 128)->nullable()->after('metadata_cid');
            $table->string('bytecode_cid', 128)->nullable()->after('source_cid');
            $table->string('deployer_address')->nullable()->after('bytecode_cid');
            $table->unsignedBigInteger('block_id')->nullable()->after('deployer_address');
            $table->unsignedBigInteger('tx_id')->nullable()->after('block_id');
            $table->json('analysis_report')->nullable()->after('tx_id');
            $table->string('standard', 32)->nullable()->after('analysis_report');
        });

        Schema::create('contract_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('wallet_address');
            $table->timestamps();
            $table->unique(['contract_id', 'wallet_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_wallets');
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'source_code', 'bytecode_hex', 'abi_json', 'status', 'compiler_version',
                'metadata_cid', 'source_cid', 'bytecode_cid', 'deployer_address',
                'block_id', 'tx_id', 'analysis_report', 'standard',
            ]);
        });
    }
};
