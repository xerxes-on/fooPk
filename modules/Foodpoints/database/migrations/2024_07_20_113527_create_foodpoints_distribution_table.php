<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('foodpoints_distribution', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->integer('amount');
            $table->unsignedTinyInteger('type')->comment('1 - weekly foodpints distribution');
            $table->timestamp('created_at')->nullable(false)->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['user_id']);
            $table->index(['created_at']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(static function () {
            Schema::dropIfExists('foodpoints_distribution');
        });
    }
};
