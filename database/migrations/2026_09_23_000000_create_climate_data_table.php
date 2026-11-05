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
        Schema::create('climate_data', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('device_id')->constrained('lorawan_devices')->cascadeOnDelete();
            $table->dateTime('date');
            $table->string('type');
            $table->string('value');
            $table->timestamps();

            // Composite index for the chart queries, which always filter by
            // type and then scan a date range.
            $table->index(['type', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('climate_data');
    }
};
