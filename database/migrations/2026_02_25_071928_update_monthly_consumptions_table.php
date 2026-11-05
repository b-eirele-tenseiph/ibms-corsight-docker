<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("UPDATE monthly_consumptions SET date = CONCAT(date, '-01') WHERE date NOT LIKE '%-%-%'");

        Schema::table('monthly_consumptions', function (Blueprint $table) {
            $table->date('date')->change();
            $table->decimal('consumption', 10, 4)->default(0)->change();
            $table->decimal('confirmed_consumption', 10, 4)->default(0)->after('consumption');
            $table->decimal('unattributed_consumption', 10, 4)->default(0)->after('confirmed_consumption');
            $table->boolean('has_unattributed')->default(false)->after('unattributed_consumption');
            $table->unique(['floor_name', 'date']);
        });

        DB::statement('UPDATE monthly_consumptions SET confirmed_consumption = consumption');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('UPDATE monthly_consumptions SET consumption = confirmed_consumption');

        Schema::table('monthly_consumptions', function (Blueprint $table) {
            $table->dropUnique(['floor_name', 'date']);
            $table->dropColumn(['confirmed_consumption', 'unattributed_consumption', 'has_unattributed']);
            $table->string('date')->change();
            $table->decimal('consumption')->change();
        });

        DB::statement("UPDATE monthly_consumptions SET date = LEFT(date, 7)");
    }
};
