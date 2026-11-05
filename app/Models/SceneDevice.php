<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SceneDevice extends Model
{
    /** @use HasFactory<\Database\Factories\SceneDeviceFactory> */
    use HasFactory, HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $table              (1.0)  Define data type casting for attributes.
    // $casts               (2.0)  Define data type casting for attributes.
    // $fillable            (3.0)  Define mass-assignable fields.
    // $keyType             (4.0)  Specify primary key type as string (UUID).
    // $incrementing        (5.0)  Indicate primary key is not auto-incrementing.
    // $device              (6.0)  Establishes a polymorphic relationship to
    //                             associate this model with multiple device types.



    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $table
     * <Function> Define the table name if it differs from the default.
     *
     * @var string
     */
    protected $table = 'scene_devices';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $casts
     * <Function> Define data type casting for attributes.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'command' => 'json',
    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $fillable
     * <Function> Define the fillable fields for mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'scene_id',
        'device_type',
        'device_id',
        'name',
        'command',
        'command_type',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $keyType
     * <Function> Specify primary key type as string (UUID).
     *
     * @var string
     */    protected $keyType = 'string';

    /**
     * <Layer number> (5.0)
     * *
     * <Processing name> $incrementing
     * <Function> Indicate that the primary key is not auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> device
     * <Function> Establishes a polymorphic relationship to associate this model with multiple device types.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function device()
    {
        return $this->morphTo();
    }
}
