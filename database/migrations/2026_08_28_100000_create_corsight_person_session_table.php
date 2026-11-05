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
        Schema::create('corsight_person_session', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('device_id')->nullable()->constrained('corsight_device')->nullOnDelete();
            $table->uuid('appearance_id');
            $table->string('camera_id');
            $table->string('status', 16)->default('in');
            $table->timestamp('enter_at');
            $table->timestamp('exit_at')->nullable();
            $table->unsignedInteger('dwell_seconds')->nullable();
            $table->timestamp('last_seen_at');
            $table->string('gender', 16)->nullable();
            $table->string('age_group', 32)->nullable();
            $table->decimal('face_score', 5, 2)->nullable();
            $table->mediumText('image')->nullable();
            $table->boolean('security_flag')->default(false);
            $table->json('watchlist_matches')->nullable();
            $table->string('poi_id')->nullable();
            $table->string('poi_name')->nullable();
            $table->decimal('poi_confidence', 5, 2)->nullable();
            $table->string('liveness', 32)->nullable();
            $table->string('mask_outcome', 32)->nullable();
            $table->unsignedInteger('event_count')->default(0);
            $table->string('first_event_id')->nullable();
            $table->string('last_event_id')->nullable();
            $table->string('person_id')->nullable();
            $table->string('merged_to_tracker')->nullable();
            $table->timestamps();

            $table->unique(['device_id', 'appearance_id']);
            $table->index(['device_id', 'status']);
            $table->index(['enter_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corsight_person_session');
    }
};
