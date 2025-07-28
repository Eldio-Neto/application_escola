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
            // Suporte a múltiplos gateways - apenas adicionar colunas que não existem
            if (!Schema::hasColumn('payments', 'gateway')) {
                $table->string('gateway')->default('getnet')->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'gateway_payment_id')) {
                $table->string('gateway_payment_id')->nullable()->after('gateway');
            }
            if (!Schema::hasColumn('payments', 'gateway_order_id')) {
                $table->string('gateway_order_id')->nullable()->after('gateway_payment_id');
            }
        });

        // Migrar dados existentes do Getnet apenas se as colunas não existem
        if (Schema::hasColumn('payments', 'getnet_payment_id')) {
            DB::statement('UPDATE payments SET gateway_payment_id = getnet_payment_id WHERE getnet_payment_id IS NOT NULL AND gateway_payment_id IS NULL');
        }
        if (Schema::hasColumn('payments', 'getnet_order_id')) {
            DB::statement('UPDATE payments SET gateway_order_id = getnet_order_id WHERE getnet_order_id IS NOT NULL AND gateway_order_id IS NULL');
        }
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
                'gateway_order_id'
            ]);
        });
    }
};
