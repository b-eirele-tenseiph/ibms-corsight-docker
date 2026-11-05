<?php

namespace App\Models;

use App\Models\Room;
use App\Models\RoomArea;
use App\Traits\Scopes\HasSwitchBotType;
use App\Traits\Scopes\RoomFilterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SwitchBotHub extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // TYPES                    (1.0) The available types for SwitchBot hub.
    // $fillable                (2.0) The attributes that are mass assignable.
    // setSerialNumberAttribute (3.0) Set the hub's serial number.
    // room                     (4.0) The room associated with the SwitchBot
    //                                hub.
    // devices                  (5.0) The SwitchBot devices associated with the
    //                                SwitchBot hub.
    // remotes                  (6.0) the SwitchBot virtual infrared remotes
    //                                associated with the SwitchBot hub.
    // roomArea                 (7.0) The room area associated with the
    //                                switchBot hub.

    use HasUuids, HasSwitchBotType, RoomFilterable, HasFactory;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> TYPES
     * <Function> The available types for SwitchBot hub.
     *
     * @var string[]
     */
    public const TYPES = [
        'Hub',
        'Hub Mini',
        'Hub Plus',
    ];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'serial_number',
        'type_id',
        'room_id',
        'room_area_id',
    ];


    /**
     * <Layer number> (3.0)
     *
     * <Processing name> setSerialNumberAttribute
     * <Function> Set the hub's serial number.
     *
     * @param string $value
     * @return void
     */
    public function setSerialNumberAttribute(string $value): void
    {
        $this->attributes['serial_number'] = strtolower($value);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> room
     * <Function> The room associated with the SwitchBot hub.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> devices
     * <Function> The SwitchBot devices associated with the SwitchBot hub.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function devices(): HasMany
    {
        return $this->hasMany(SwitchBotDevice::class, 'hub_id', 'id');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> remotes
     * <Function> The SwitchBot virtual infrared remotes associated with the
     *            SwitchBot hub.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function remotes(): HasMany
    {
        return $this->hasMany(SwitchBotRemote::class, 'hub_id', 'id');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> roomArea
     * <Function> The room area associated with the switchBot hub.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function roomArea(): BelongsTo
    {
        return $this->belongsTo(RoomArea::class, 'room_area_id', 'id');
    }
}
