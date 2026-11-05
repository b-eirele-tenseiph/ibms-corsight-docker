<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSwitchBotLockAccessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('switch_bot_lock_accesses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->integer('passcode_id');
            $table->string('name');
            $table->foreignUuid('device_id')->constrained('switch_bot_devices')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('access_pin');
            $table->string('access_type');
            $table->timestamp('arrival_time')->nullable();
            $table->timestamp('departure_time')->nullable();
            $table->string('status')->default('inactive');
            $table->string('command_id')->nullable();
            $table->integer('retry')->default(0);
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
        Schema::dropIfExists('switch_bot_lock_accesses');
    }
}
