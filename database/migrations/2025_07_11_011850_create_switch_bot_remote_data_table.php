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
        Schema::create('switch_bot_remote_data', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('device_id')->constrained('switch_bot_remotes')->cascadeOnDelete();
            $table->string('via');
            $table->json('context');
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'via'], 'switch_bot_remote_data_device_id_via_index');
            $table->index(['device_id', 'received_at'], 'switch_bot_remote_data_device_id_received_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('switch_bot_remote_data', function (Blueprint $table) {
            $table->dropForeign(['device_id']);

            // Drop the indexes
            $table->dropIndex('switch_bot_remote_data_device_id_via_index');
            $table->dropIndex('switch_bot_remote_data_device_id_received_at');
        });

        Schema::dropIfExists('switch_bot_remote_data');
    }
};
