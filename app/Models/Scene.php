<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scene extends Model
{
    /** @use HasFactory<\Database\Factories\SceneFactory> */
    use HasFactory, HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $table                 (1.0)  Define the table name if it differs from default.
    // $primaryKey            (2.0)  Explicitly set the primary key.
    // $incrementing          (3.0)  Indicate primary key is not auto-incrementing.
    // $keyType               (4.0)  Specify primary key type as string (UUID).
    // $fillable              (5.0)  Define mass-assignable fields.
    // room                   (6.0)  Relationship: A scene belongs to a room.
    // sceneDevices           (7.0)  Relationship: A scene has many scene devices.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $table
     * <Function> Define the table name if it differs from the default.
     *
     * @var string
     */
    protected $table = 'scenes';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $primaryKey
     * <Function> Explicitly set the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $incrementing
     * <Function> Indicate that the primary key is not auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $keyType
     * <Function> Indicate that the primary key is a string (UUID).
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $fillable
     * <Function> Define the fillable fields for mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'context',
        'name',
        'room_id',
        'scheduled',
        'days',
        'time'
    ];

    protected $casts = [
        'context' => 'json',
        'scheduled' => 'boolean',
    ];

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> room
     * <Function> Relationship: A scene belongs to a room.
     *
     * @return BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> sceneDevices
     * <Function> Relationship: A scene has many scene devices.
     *
     * @return HasMany
     */
    public function sceneDevices()
    {
        return $this->hasMany(SceneDevice::class, 'scene_id', 'id');
    }
}
