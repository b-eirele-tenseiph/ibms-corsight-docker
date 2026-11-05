<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyPeopleFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_people_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('device_id')->nullable()->constrained('lorawan_devices')->nullOnDelete();
            $table->date('date');
            $table->json('context');
            $table->timestamps();

            // Add index to date
            $table->index('date', 'idx_daily_people_flows_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_people_flows', function (Blueprint $table) {
            $table->dropIndex('idx_daily_people_flows_date');
        });

        Schema::dropIfExists('daily_people_flows');
    }
}
