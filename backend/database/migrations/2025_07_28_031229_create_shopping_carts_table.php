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
        Schema::create('shopping_carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // For guest users
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // For authenticated users
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2); // Price at time of adding to cart
            $table->integer('quantity')->default(1);
            $table->timestamp('expires_at')->nullable(); // Cart expiration
            $table->timestamps();
            
            $table->index(['session_id', 'user_id']);
            $table->unique(['session_id', 'course_id'], 'unique_session_course');
            $table->unique(['user_id', 'course_id'], 'unique_user_course');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_carts');
    }
};
