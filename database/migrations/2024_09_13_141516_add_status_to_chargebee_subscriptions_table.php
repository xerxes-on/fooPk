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
        Schema::table('chargebee_subscriptions', static function (Blueprint $table) {
            $table->enum('status', ['future','in_trial','active','non_renewing','paused','cancelled','transferred'])->nullable()->after('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chargebee_subscriptions', static function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
