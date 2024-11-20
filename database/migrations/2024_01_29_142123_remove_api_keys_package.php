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
        Schema::dropIfExists('api_key_admin_events');
        Schema::dropIfExists('api_key_access_events');
        Schema::dropIfExists('api_keys');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('key', 64);
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('key');
        });

        Schema::create('api_key_access_events', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('api_key_id');
            $table->ipAddress('ip_address');
            $table->text('url');
            $table->timestamps();

            $table->index('ip_address');
            $table->foreign('api_key_id')->references('id')->on('api_keys');
        });

        Schema::create('api_key_admin_events', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('api_key_id');
            $table->ipAddress('ip_address');
            $table->string('event');
            $table->timestamps();

            $table->index('ip_address');
            $table->index('event');
            $table->foreign('api_key_id')->references('id')->on('api_keys');
        });
    }
};
