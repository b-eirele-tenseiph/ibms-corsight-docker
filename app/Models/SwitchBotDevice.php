<?php

namespace App\Models;

use App\Models\{Room, RoomArea};
use App\Traits\Relations\{HasGridStackPosition, HasLatestData};
use App\Traits\Scopes\{RoomFilterable, HasSwitchBotType};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};

class SwitchBotDevice extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // TYPES                 (1.0)  The avaiable types for SwitchBot device.
    // $guarded              (2.0)  The attributes that are not mass assignable.
    // $casts                (3.0)  The attributes that should be cast.
    // $appends              (4.0)  The custom accessor that should be append.
    // serialNumber          (5.0)  Get and Set the device's serial number.
    // hub                   (6.0)  The SwitchBot hub associated with the
    //                              SwitchBot device.
    // room                  (7.0)  The room associated with the SwitchBot
    //                              device.
    // roomArea              (8.0)  The room area associated with the
    //                              Switchbot device.
    // data                  (9.0) The data collection of the SwitchBot
    //                             device.
    // latestData            (10.0) The latest data of the SwitchBot device.
    // scopeOnline           (11.0) Scope a query to only include SwitchBot
    //                              devices with the given status.
    // getIsActiveAttribute  (12.0) Custom accessor for classifying if
    //                              switchbot device is active.
    // scopeInRoom           (13.0) Custom accessor for classifying switchbot
    //                              device.
    // classification        (14.0) Get device classification according to
    //                              type and other factor.
    // loadTopNData          (15.0) Get top N data entries for a collection
    //                              of devices.

    use HasFactory,
        HasUuids,
        HasSwitchBotType,
        RoomFilterable,
        HasGridStackPosition,
        HasLatestData;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> TYPES
     * <Function> The available types for SwitchBot device.
     *
     * @var string[]
     */
    const TYPES = [
        'Bot',
        'Contact Sensor',
        'Color Bulb',
        'Blind Tilt',
        'Curtain',
        'Humidifier',
        'Lock',
        'Meter',
        'Meter Plus',
        'Motion Sensor',
        'Plug',
        'Plug Mini (JP)',
        'Plug Mini (US)',
        'Smart Fan',
        'Strip Light',
        'Keypad',
        'Keypad Touch',
        'Smart Lock',
        'Smart Lock Pro',
        'WoIOSensor'
    ];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $guarded
     * <Function> The attributes that are not mass assignable.
     *
     * @var string[]
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
        'context' => 'array',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> serialNumber
     * <Function> Get and Set the device's serial number.
     *
     * @param  string  $value
     */
    protected function serialNumber(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtolower($value),
        );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> hub
     * <Function> The SwitchBot hub associated with the SwitchBot device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hub(): BelongsTo
    {
        return $this->belongsTo(SwitchBotHub::class, 'hub_id', 'id');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> room
     * <Function> The room associated with the SwitchBot device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> roomArea
     * <Function> The room area associated with the SwitchBot device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function roomArea(): BelongsTo
    {
        return $this->belongsTo(RoomArea::class, 'room_area_id', 'id');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> data
     * <Function> The data collection of the SwitchBot device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data(): HasMany
    {
        return $this->hasMany(SwitchBotDeviceData::class, 'device_id', 'id');
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> classification
     * <Function> Get device classification according to type and other factor
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function classification(): Attribute
    {
        return Attribute::make(
            get: function () {

                $type = $this->type;

                return match ($type->name) {

                    // Devices
                    'Plug', 'Plug Mini (JP)', 'Plug Mini (US)', 'Smart Lock', 'Smart Lock Pro', 'Bot', 'Keypad', 'Keypad Touch' => 'device',

                    // Curtain
                    'Curtain' => 'curtain',

                    //Blind Tilt
                    'Blind Tilt' => 'blindtilt',

                    // Thermohygrometer
                    'WoIOSensor' => 'thermohygrometer',

                    default => $type
                };
            }
        );
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> loadTopNData
     * <Function> Get top N data entries for a collection of devices.
     *
     * @return void
     */
    public static function loadTopNData(Collection $devices, int $limit = 10): void
    {
        $deviceIds = $devices->pluck('id');

        $rows = SwitchBotDeviceData::query()
            ->fromSub(function ($query) use ($deviceIds) {
                $query->select('*')
                    ->selectRaw('ROW_NUMBER() OVER (PARTITION BY device_id ORDER BY created_at DESC) as row_num')
                    ->from('switch_bot_device_data')
                    ->whereIn('device_id', $deviceIds);
            }, 'ranked')
            ->where('row_num', '<=', $limit)
            ->orderBy('device_id')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('device_id');

        foreach ($devices as $d) {
            $d->setRelation('top_data', $rows->get($d->id, collect()));
        }
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> switchBotLockAccess
     * <Function> The data collection of the SwitchBot Lock Access.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function switchBotLockAccess()
    {
        return $this->hasMany(SwitchBotLockAccess::class, 'device_id', 'id');
    }
}
