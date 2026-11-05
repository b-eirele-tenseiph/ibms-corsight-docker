<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSwitchBotHubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('switch_bot_hubs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->macAddress('serial_number')->unique();
            $table->string('type_id')->nullable();
            $table->string('room_id')->nullable();
            $table->string('room_area_id')->nullable();
            $table->boolean('online_flag');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('switch_bot_hubs');
    }
}
