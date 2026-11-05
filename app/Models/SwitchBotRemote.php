<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\Relations\{HasGridStackPosition, HasLatestData};
use App\Traits\Scopes\{RoomFilterable, HasSwitchBotType};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasOneThrough, HasMany, BelongsTo};
use Illuminate\Support\Str;

class SwitchBotRemote extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // TYPES                         (1.0)  The avaiable types for SwitchBot
    //                                      remote.
    // $guarded                      (2.0)  The attributes that are not mass
    //                                      assignable.
    // hub                           (3.0)  The SwitchBot hub associated with
    //                                      the SwitchBot remote.
    // room                          (4.0)  The room associated with the
    //                                      SwitchBot remote.
    // roomArea                      (5.0)  The room area associated with the
    //                                      Switchbot remote.
    // data                          (6.0)  The data collection of the SwitchBot
    //                                      remote.
    // type                          (7.0) The SwitchBotType associated with
    //                                      the SwitchBot remote.
    // classification                (8.0) Get device classification according
    //                                      to type and other factor.

    use HasFactory,
        HasSwitchBotType,
        RoomFilterable,
        HasUuids,
        HasGridStackPosition,
        HasLatestData;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> TYPES
     * <Function> The available types for SwitchBot remote.
     *
     * @var string[]
     */
    public const TYPES = [
        'Air Conditioner',
        'Air Purifier',
        'Camera',
        'DVD',
        'Fan',
        'IPTV/Streamer',
        'Light',
        'Others',
        'TV',
        'Projector',
        'Set Top Box',
        'Speaker',
        'Vacuum Cleaner',
        'Water Heater',
    ];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are not mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> hub
     * The SwitchBot hub associated with the SwitchBot remote.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hub(): BelongsTo
    {
        return $this->belongsTo(SwitchBotHub::class, 'hub_id', 'id');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> room
     * <Function> The room associated with the SwitchBot remote.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function room(): HasOneThrough
    {
        return $this->hasOneThrough(
            Room::class,
            SwitchBotHub::class,
            'id',
            'id',
            'hub_id',
            'room_id'
        );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> roomArea
     * <Function> The room area associated with the Switchbot remote.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function roomArea(): HasOneThrough
    {
        return $this->hasOneThrough(
            RoomArea::class,
            SwitchBotHub::class,
            'id',
            'id',
            'hub_id',
            'room_area_id'
        );
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> data
     * <Function> The data collection of the SwitchBot remote.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data(): HasMany
    {
        return $this->hasMany(SwitchBotRemoteData::class, 'device_id', 'id');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> type
     * <Function> The SwitchBotType associated with the SwitchBot remote.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(SwitchBotType::class, 'type_id');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> classification
     * <Function> Get device classification according to type and other factor.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function classification(): Attribute
    {
        return Attribute::make(
            get: function () {

                $type = $this->type;
                $name = $this->name;

                return match ($type->name) {
                    // Air Conditioner
                    'Air Conditioner' => 'aircon',

                    // Others
                    'Others' => (function () use ($name) {
                        // Check name
                        return match (true) {
                            Str::contains($name, ['Projector', 'projector'], true) => 'projector',
                            Str::contains($name, ['Television', 'TV', 'television'], true) => 'television',
                            default => 'unsupported'
                        };
                    })(),

                    default => $type
                };
            }
        );
    }
}
