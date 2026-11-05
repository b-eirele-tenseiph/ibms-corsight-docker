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
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign(['foods_id']);
            $table->dropColumn('foods_id');
            $table->foreignUuid('food_id')->nullable()->constrained('foods')->nullOnDelete();
            $table->decimal('total_amount')->before('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign(['food_id']);
            $table->dropColumn('food_id');
            $table->uuid('foods_id')->nullable();
            $table->foreign('foods_id')->references('id')->on('order_details')->nullOnDelete();
        });
    }
};
