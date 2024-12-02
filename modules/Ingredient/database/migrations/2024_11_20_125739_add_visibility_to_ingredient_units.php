<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ingredient_units', static function (Blueprint $table) {
            $table->tinyInteger('visibility', unsigned: true)
                ->after('type')
                ->default(1)
                ->comment('Describes if the unit is visible on frontend')
                ->nullable(false);
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_units', static function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
