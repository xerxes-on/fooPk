<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ingredient_translations', static function (Blueprint $table) {
            $table->string('name_plural', 191)->nullable(false)->default('');
        });
        Schema::table('ingredients', static function (Blueprint $table) {
            $table->unsignedSmallInteger('unit_amount')->after('unit_id')->nullable(false)->default(0)->comment('How many unit amount is required to have 1 piece of ingredient');
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_translations', static function (Blueprint $table) {
            $table->dropColumn('name_plural');
        });
        Schema::table('ingredients', static function (Blueprint $table) {
            $table->dropColumn('unit_amount');
        });
    }
};
