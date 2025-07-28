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
            $table->string('gateway')->default('getnet')->after('payment_method');
            $table->string('gateway_payment_id')->nullable()->after('gateway');
            $table->string('gateway_order_id')->nullable()->after('gateway_payment_id');
            $table->json('gateway_response')->nullable()->after('gateway_order_id');
        });

        // Migrar dados existentes
        DB::table('payments')->update([
            'gateway_payment_id' => DB::raw('getnet_payment_id'),
            'gateway_order_id' => DB::raw('getnet_order_id'),
        ]);
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
                'gateway_response'
            ]);
        });
    }
};
