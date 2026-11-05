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
        Schema::create('hourly_consumptions', function (Blueprint $table) {
            $table->id();
            $table->text('device_id')->nullable();
            $table->string('floor_name');
            $table->dateTime('date');
            $table->json('consumption');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hourly_consumptions');
    }
};
