<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {

    function isFK(string $table, string $column): bool
    {
        $fkColumns = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys($table);

        return collect($fkColumns)->map(function ($fkColumn) {
            return $fkColumn->getColumns();
        })->flatten()->contains($column);
    }

    public function up(): void
    {
        if ($this->isFK('user_to_challenge', 'challenge_id')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Schema::table('user_to_challenge', function (Blueprint $table) {
                $table->dropForeign(['challenge_id']);
            });
            Schema::table('user_to_challenge', function (Blueprint $table) {
                $table->foreign('challenge_id')->references('id')->on('abo_challenges');
            });
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function down(): void
    {
        if ($this->isFK('user_to_challenge', 'challenge_id')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Schema::table('user_to_challenge', function (Blueprint $table) {
                $table->dropForeign(['challenge_id']);
            });

            Schema::table('user_to_challenge', function (Blueprint $table) {
                $table->foreign('challenge_id')->references('id')->on('abo_subscriptions');
            });
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};
