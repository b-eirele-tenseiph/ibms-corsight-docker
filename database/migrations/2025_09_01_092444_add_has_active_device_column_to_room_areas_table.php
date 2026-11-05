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
        $table = 'room_areas';

        // Drop column for device_status
        if (Schema::hasColumn($table, 'device_status')) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('device_status');
            });
        }

        Schema::table($table, function (Blueprint $table) {
            $table->boolean('has_active_device')->default(0)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = 'room_areas';

        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn('has_active_device');
        });
    }
};
