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
        $tables = [
            'lorawan_devices' => 'room_area_id',
            'switch_bot_devices' => 'hub_id',
            'switch_bot_remotes' => 'hub_id'
        ];

        foreach ($tables as $tableName => $afterColumn) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $afterColumn) {
                $table->boolean('enable_data_storage')->default(1)->after($afterColumn);

                if (Schema::hasColumn($tableName, 'status')) $table->dropColumn('status');
                if (Schema::hasColumn($tableName, 'online_flag')) $table->dropColumn('online_flag');
            });
        }

        Schema::table('switch_bot_hubs', function (Blueprint $table) {
            if (Schema::hasColumn('switch_bot_hubs', 'online_flag')) $table->dropColumn('online_flag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['lorawan_devices', 'switch_bot_devices', 'switch_bot_remotes'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('enable_data_storage');

                $table->string('status')->nullable();
                $table->boolean('online_flag')->default(0);
            });
        }

        Schema::table('switch_bot_hubs', function (Blueprint $table) {
            $table->boolean('online_flag')->default(0);
        });
    }
};
