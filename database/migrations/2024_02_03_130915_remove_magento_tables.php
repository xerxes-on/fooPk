<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('order_histories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('orders_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id');
            $table->string('increment_id', 32);
            $table->timestamps();
            $table->index(['user_id', 'order_id']);
        });
    }
};
