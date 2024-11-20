<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('recipes', static function (Blueprint $table) {
            $table->dropIndex(['draft']);
        });
        Schema::table('recipes', static function (Blueprint $table) {
            $table->renameColumn('draft', 'status');
        });
        Schema::table('recipes', static function (Blueprint $table) {
            DB::statement('ALTER TABLE `recipes` MODIFY `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;');
        });

        DB::table('recipes')->update(['status' => DB::raw('CASE WHEN status = 1 THEN 0 ELSE 1 END')]);

        Schema::table('recipes', static function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', static function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('recipes', static function (Blueprint $table) {
            $table->boolean('status')->nullable()->change();
        });
        Schema::table('recipes', static function (Blueprint $table) {
            $table->renameColumn('status', 'draft');
        });

        DB::table('recipes')->update(['draft' => DB::raw('CASE WHEN draft = 1 THEN 0 ELSE 1 END')]);

        Schema::table('recipes', static function (Blueprint $table) {
            $table->index('draft');
        });
    }
};
