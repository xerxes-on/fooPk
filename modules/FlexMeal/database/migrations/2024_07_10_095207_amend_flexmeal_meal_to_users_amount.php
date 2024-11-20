<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('flexmeal_to_users', static function (Blueprint $table) {
            $table->unsignedSmallInteger('amount')->nullable(false)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('flexmeal_to_users', static function (Blueprint $table) {
            $table->unsignedFloat('amount')->nullable()->change();
        });
    }
};
