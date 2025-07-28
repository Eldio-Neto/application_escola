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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed']); 
            $table->decimal('value', 10, 2); // Percentage or fixed amount
            $table->decimal('minimum_amount', 10, 2)->nullable(); // Minimum cart amount
            $table->integer('usage_limit')->nullable(); // Total usage limit
            $table->integer('used_count')->default(0); // Current usage count
            $table->integer('usage_limit_per_user')->nullable(); // Per user limit
            $table->datetime('valid_from');
            $table->datetime('valid_until');
            $table->boolean('active')->default(true);
            $table->json('category_ids')->nullable(); // Applicable categories
            $table->json('course_ids')->nullable(); // Applicable specific courses
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
