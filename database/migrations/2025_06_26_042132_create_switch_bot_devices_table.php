<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('switch_bot_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('serial_number')->unique();
            $table->string('context')->nullable();
            $table->string('status')->nullable();
            $table->string('room_id')->nullable();
            $table->string('room_area_id')->nullable();
            $table->string('type_id')->nullable();
            $table->string('hub_id')->nullable();
            $table->boolean('online_flag');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('switch_bot_devices');
    }
};
