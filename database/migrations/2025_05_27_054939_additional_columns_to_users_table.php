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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->text('password_displayable')->nullable()->after('password');
            $table->timestamp('password_last_updated_at')->nullable()->after('password');
            $table->boolean('ads_flag')->default(true)->after('remember_token');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
            $table->dropColumn('password_displayable');
            $table->dropColumn('password_last_updated_at');
            $table->dropColumn('ads_flag');
            $table->dropSoftDeletes();
        });
    }
};
