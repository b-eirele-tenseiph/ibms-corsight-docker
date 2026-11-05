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
        Schema::table('admins', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // update any NULL values to a default value
            DB::table('admins')
                ->whereNull('email')
                ->update(['email' => 'default@example.com']);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
