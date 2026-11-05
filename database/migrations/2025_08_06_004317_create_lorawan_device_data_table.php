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
        Schema::create('lorawan_device_data', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('device_id')->constrained('lorawan_devices')->cascadeOnDelete();
            $table->string('transmission');
            $table->string('type');
            $table->json('context');
            $table->timestamp('received_at')->useCurrent();

            $table->index('type', 'idx_type');
            $table->index(['device_id', 'type', 'id'], 'lorawan_device_data_device_type_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lorawan_device_data', function (Blueprint $table) {
            $table->dropForeign(['device_id']);

            $table->dropIndex('idx_type');
            $table->dropIndex('lorawan_device_data_device_type_id_index');
        });

        Schema::dropIfExists('lorawan_device_data');
    }
};
