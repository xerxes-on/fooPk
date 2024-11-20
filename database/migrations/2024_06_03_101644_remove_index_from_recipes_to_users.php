<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('recipes_to_users', static function (Blueprint $table) {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $schemaManager->listTableIndexes('recipes_to_users');

            if (array_key_exists('user_id_meal_date_meal_time_challenge_id', $indexesFound)) {
                $table->dropIndex('user_id_meal_date_meal_time_challenge_id');
            }
        });
    }

    public function down(): void
    {
        //
    }
};
