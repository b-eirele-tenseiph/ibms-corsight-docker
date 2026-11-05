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
        Schema::create('corsight_device_data', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique()->nullable();
            $table->string('camera_id');
            $table->foreignUuid('device_id')->nullable()->constrained('corsight_device')->nullOnDelete();
            $table->string('event_type');
            $table->json('context')->nullable();
            $table->timestamp('received_at')->useCurrent();

            $table->index('event_type', 'idx_event_type');
            $table->index(['camera_id', 'event_type', 'id'], 'corsight_device_data_camera_event_type_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corsight_device_data', function (Blueprint $table) {
            $table->dropForeign(['device_id']);

            $table->dropIndex('idx_event_type');
            $table->dropIndex('corsight_device_data_camera_event_type_id_index');
        });

        Schema::dropIfExists('corsight_device_data');
    }
};
