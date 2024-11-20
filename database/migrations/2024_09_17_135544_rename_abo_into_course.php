<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('abo_challenges_translations')) {
            // drop old foreign and perform a rename
            Schema::table('abo_challenges_translations', static function (Blueprint $table) {
                $table->dropForeign(['abo_challenges_id']);
                $table->renameColumn('abo_challenges_id', 'course_id');
            });
        }
        if (Schema::hasTable('abo_challenges_articles')) {
            Schema::table('abo_challenges_articles', static function (Blueprint $table) {
                $table->dropForeign(['abo_challenge_id']);
                $table->renameColumn('abo_challenge_id', 'course_id');
            });
        }
        if (Schema::hasTable('abo_challenges_users')) {
            Schema::table('abo_challenges_users', static function (Blueprint $table) {
                $table->dropForeign(['abo_challenge_id']);
                $table->renameColumn('abo_challenge_id', 'course_id');
            });
        }
        if (Schema::hasTable('abo_challenges')) {
            // rename tables
            Schema::rename('abo_challenges', 'courses');
            Schema::rename('abo_challenges_translations', 'course_translations');
            Schema::rename('abo_challenges_articles', 'course_articles');
            Schema::rename('abo_challenges_users', 'course_users');
        }
        if (Schema::hasTable('course_translations')) {
            // adding new foreign key
            Schema::table('course_translations', static function (Blueprint $table) {
                $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            });
            Schema::table('course_articles', static function (Blueprint $table) {
                $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            });
            Schema::table('course_users', static function (Blueprint $table) {
                $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('abo_subscriptions')) {
            Schema::table('user_to_challenge', static function (Blueprint $table) {
                $table->dropForeign('user_to_challenge_user_id_foreign');
                $table->dropColumn('challenge_id');
            });
            Schema::dropIfExists('abo_subscriptions');
        }
        Schema::rename('user_to_challenge', 'user_subscriptions');

        DB::table('courses')->update(['status' => DB::raw('CASE WHEN status = 1 THEN 0 ELSE 1 END')]);
    }

    public function down(): void
    {
        // drop old foreign and perform a rename
        Schema::table('course_translations', static function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->renameColumn('course_id', 'abo_challenges_id');
        });
        Schema::table('course_articles', static function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->renameColumn('course_id', 'abo_challenge_id');
        });
        Schema::table('course_users', static function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->renameColumn('course_id', 'abo_challenge_id');
        });

        // rename tables
        Schema::rename('courses', 'abo_challenges');
        Schema::rename('course_translations', 'abo_challenges_translations');
        Schema::rename('course_articles', 'abo_challenges_articles');
        Schema::rename('course_users', 'abo_challenges_users');
        Schema::rename('user_subscriptions', 'user_to_challenge');

        // adding new foreign key
        Schema::table('abo_challenges_translations', static function (Blueprint $table) {
            $table->foreign('abo_challenges_id')->references('id')->on('abo_challenges');
        });
        Schema::table('abo_challenges_articles', static function (Blueprint $table) {
            $table->foreign('abo_challenge_id')->references('id')->on('abo_challenges');
        });
        Schema::table('abo_challenges_users', static function (Blueprint $table) {
            $table->foreign('abo_challenge_id')->references('id')->on('abo_challenges');
        });

        DB::table('abo_challenges')->update(['status' => DB::raw('CASE WHEN status = 0 THEN 1 ELSE 0 END')]);
    }
};
