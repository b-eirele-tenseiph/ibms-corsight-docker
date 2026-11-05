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
        Schema::create('lorawan_gateways', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('floor_id')->nullable();
            $table->ipAddress('ip_address')->unique();
            $table->macAddress('mac_address')->unique();
            $table->string('connection');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lorawan_gateways');
    }
};
