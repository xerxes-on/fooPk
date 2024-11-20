<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ingredient_hints', static function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('ingredient_id');

            $table->foreign('ingredient_id')->references('id')->on('ingredients')->cascadeOnDelete();

            $table->timestamp('created_at')->nullable(false)->useCurrent();
            $table->timestamp('updated_at')->nullable(false)->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('ingredient_hint_translations', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ingredient_hint_id');

            $table->foreign('ingredient_hint_id')->references('id')->on('ingredient_hints')->cascadeOnDelete();

            $table->string('locale')->index();
            $table->text('content');
            $table->string('link_url');
            $table->string('link_text');

            $table->unique(['ingredient_hint_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_hint_translations');
        Schema::dropIfExists('ingredient_hints');
    }
};
