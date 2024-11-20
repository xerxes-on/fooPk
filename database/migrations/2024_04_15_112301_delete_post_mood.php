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
        Schema::table('posts', static function (Blueprint $blueprint) {
            $blueprint->dropColumn('mood');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', static function (Blueprint $blueprint) {
            $blueprint->unsignedTinyInteger('mood')->nullable(false)->default(1)->after('content');
        });
    }
};
