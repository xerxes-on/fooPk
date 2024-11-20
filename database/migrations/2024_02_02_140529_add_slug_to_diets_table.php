<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('diets', function (Blueprint $table) {
            $table->string('slug', 30)->after('id');
        });
        DB::table('diet_translations')
            ->where('locale', 'en')
            ->get(['diet_id', 'name'])
            ->each(function ($diet) {
                DB::table('diets')
                    ->where('id', $diet->diet_id)
                    ->update(['slug' => Str::slug(str_replace(' ', '_', sanitize_string($diet->name)), '_')]);
            });
        Schema::table('diets', function (Blueprint $table) {
            $table->string('slug', 30)->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diets', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
