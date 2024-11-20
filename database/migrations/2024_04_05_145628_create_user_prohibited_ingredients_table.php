<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Ingredient\Jobs\RecalculateUsersForbiddenIngredientsJob;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('user_prohibited_ingredients');
        Schema::dropIfExists('bug_reports');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::create('user_prohibited_ingredients', static function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('user_id');

            $table->unsignedInteger('ingredient_id');
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->cascadeOnDelete();

            $table->timestamp('created_at')->nullable(false)->useCurrent();
        });

        RecalculateUsersForbiddenIngredientsJob::dispatch()->delay(now()->addMinutes(2));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('user_prohibited_ingredients');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
