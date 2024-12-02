<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('course_users', static function (Blueprint $table) {
            $table->smallInteger('counter', false, true)
                ->default(0)
                ->comment('Number of times user has taken this course (0 = first time)')
                ->after('course_id');
        });
    }

    public function down(): void
    {
        Schema::table('course_users', static function (Blueprint $table) {
            $table->dropColumn('counter');
        });
    }
};
