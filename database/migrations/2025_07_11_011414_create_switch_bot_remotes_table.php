<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSwitchBotRemotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('switch_bot_remotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('serial_number')->unique();
            $table->string('status')->nullable();
            $table->foreignUuid('type_id')->constrained('switch_bot_types')->cascadeOnDelete();
            $table->foreignUuid('hub_id')->constrained('switch_bot_hubs')->cascadeOnDelete();
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
        Schema::dropIfExists('switch_bot_remotes');
    }
}
