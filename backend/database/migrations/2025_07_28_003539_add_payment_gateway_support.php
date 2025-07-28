<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Suporte a múltiplos gateways
            $table->string('gateway')->default('getnet')->after('payment_method');
            $table->string('gateway_payment_id')->nullable()->after('gateway');
            $table->string('gateway_order_id')->nullable()->after('gateway_payment_id');
            $table->json('gateway_response')->nullable()->after('gateway_order_id');
            
            // Colunas PIX
            $table->text('pix_qr_code')->nullable()->after('due_date');
            $table->text('pix_qr_code_image')->nullable()->after('pix_qr_code');
            $table->text('pix_copy_paste')->nullable()->after('pix_qr_code_image');
            
            // Colunas de controle
            $table->text('webhook_data')->nullable()->after('pix_copy_paste');
            $table->text('error_message')->nullable()->after('webhook_data');
        });

        // Migrar dados existentes do Getnet
        DB::statement('UPDATE payments SET gateway_payment_id = getnet_payment_id WHERE getnet_payment_id IS NOT NULL');
        DB::statement('UPDATE payments SET gateway_order_id = getnet_order_id WHERE getnet_order_id IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'gateway',
                'gateway_payment_id',
                'gateway_order_id', 
                'gateway_response',
                'pix_qr_code',
                'pix_qr_code_image', 
                'pix_copy_paste',
                'webhook_data',
                'error_message'
            ]);
        });
    }
};
