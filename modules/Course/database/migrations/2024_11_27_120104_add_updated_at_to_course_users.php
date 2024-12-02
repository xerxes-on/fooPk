<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('course_users', static function (Blueprint $table) {
            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('course_users', static function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
