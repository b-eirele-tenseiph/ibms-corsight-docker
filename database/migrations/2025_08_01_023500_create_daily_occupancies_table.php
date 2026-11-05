<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyOccupanciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_occupancies', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('room_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->json('context');
            $table->timestamps();

            // Add index to date
            $table->index('date', 'idx_daily_occupancies_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_occupancies', function (Blueprint $table) {
            $table->dropIndex('idx_daily_occupancies_date');
        });
        Schema::dropIfExists('daily_occupancies');
    }
}
