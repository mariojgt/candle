<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->foreignId('site_id');
            $table->string('session_id');
            $table->string('user_id')->nullable();
            $table->string('url')->nullable();
            $table->string('referrer')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('os')->nullable();
            $table->string('os_version')->nullable();
            $table->string('device_type')->nullable();
            $table->integer('screen_width')->nullable();
            $table->integer('screen_height')->nullable();
            $table->string('language')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();

            // Add indexes for better query performance
            $table->index('event_name');
            $table->index('session_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
};
