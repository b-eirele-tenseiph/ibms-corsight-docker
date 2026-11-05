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
        Schema::create('electric_meter_data', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('device_id')->constrained('lorawan_devices')->cascadeOnDelete();
            $table->string('transmission');
            $table->string('type');
            $table->json('context');
            $table->timestamp('received_at')->useCurrent();

            $table->index(['device_id', 'type', 'received_at'], 'electric_meter_data_device_type_received_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('electric_meter_data', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->dropIndex('electric_meter_data_device_type_received_index');
        });

        Schema::dropIfExists('electric_meter_data');
    }
};
