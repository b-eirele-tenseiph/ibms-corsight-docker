<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorsightPersonSession extends Model
{
    use HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $table              (1.0)  The table associated with the model.
    // $guarded            (2.0)  The attributes that are not mass assignable.
    // $casts              (3.0)  The attributes that should be cast.
    // scopeIn             (4.0)  Limit the query to open sessions.
    // scopeExited         (5.0)  Limit the query to cleanly closed sessions.
    // scopeAbandoned      (6.0)  Limit the query to failure-closed sessions.
    // scopeCountable      (7.0)  Limit the query to sessions that count
    //                            toward enter/exit totals (excludes merged
    //                            trackers).
    // device              (8.0)  The Corsight device the visit occurred on.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $table
     * <Function> The table associated with the model.
     *
     * @var string
     */
    protected $table = 'corsight_person_session';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $guarded
     * <Function> The attributes that are not mass assignable.
     *
     * @var array<int,string>
     */
    protected $guarded = [];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'enter_at'        => 'datetime',
        'exit_at'         => 'datetime',
        'last_seen_at'    => 'datetime',
        'dwell_seconds'   => 'integer',
        'event_count'     => 'integer',
        'face_score'      => 'decimal:2',
        'poi_confidence'  => 'decimal:2',
        'watchlist_matches' => 'array',
        'security_flag'   => 'boolean',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> scopeIn
     * <Function> Limit the query to open sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeIn(Builder $query): Builder
    {
        return $query->where('status', 'in');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> scopeExited
     * <Function> Limit the query to cleanly closed sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeExited(Builder $query): Builder
    {
        return $query->where('status', 'exited');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> scopeAbandoned
     * <Function> Limit the query to failure-closed sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeAbandoned(Builder $query): Builder
    {
        return $query->where('status', 'abandoned');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> scopeCountable
     * <Function> Limit the query to sessions that count toward enter/exit
     *            totals — sessions Fortify merged into a longer tracker
     *            represent the same physical presence and are excluded.
     *
     * @param \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeCountable(Builder $query): Builder
    {
        return $query->whereNull('merged_to_tracker');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> device
     * <Function> The Corsight device the visit occurred on.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(CorsightDevice::class, 'device_id', 'id');
    }
}
