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
        Schema::create('scene_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('scene_id')->nullable()->constrained('scenes')->nullOnDelete();
            $table->string('device_type');
            $table->uuid('device_id');
            $table->string('name');
            $table->json('command');
            $table->string('command_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scene_devices');
    }
};
