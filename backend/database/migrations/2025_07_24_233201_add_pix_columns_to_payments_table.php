<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('pix_qr_code')->nullable()->after('due_date');
            $table->text('pix_qr_code_image')->nullable()->after('pix_qr_code');
            $table->text('pix_copy_paste')->nullable()->after('pix_qr_code_image');
            $table->text('webhook_data')->nullable()->after('pix_copy_paste');
            $table->text('error_message')->nullable()->after('webhook_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'pix_qr_code',
                'pix_qr_code_image', 
                'pix_copy_paste',
                'webhook_data',
                'error_message'
            ]);
        });
    }
};
