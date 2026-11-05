<?php

namespace App\Models;

use App\Traits\Scopes\{HasSettings, HasLorawanDeviceType, RoomFilterable};
use App\Traits\Relations\{HasGridStackPosition, HasLatestData};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Casts\{AsArrayObject, Attribute};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class LorawanDevice extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $fillable                  (1.0)  Attributes that are mass assignable.
    // $casts                     (2.0)  Attributes casting definitions.
    // manufacturer               (3.0)  The manufacturer of the LoRaWAN device.
    // gateway                    (4.0)  The LoRaWAN gateway associated with the
    //                                   device.
    // room                       (5.0)  The room associated with the device.
    // roomArea                   (6.0)  The room area associated with the
    //                                   device.
    // data                       (7.0)  Data log relationship for the device.
    // classification             (8.0)  Get device classification according to
    //                                   model and other factor
    // electricMeterData          (9.0)  Electric meter data log.
    // loadTopNData               (10.0) Attach top N data per type to each

    use HasFactory,
        HasUuids,
        RoomFilterable,
        HasSettings,
        HasGridStackPosition,
        HasLorawanDeviceType,
        HasLatestData;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'dev_eui',
        'manufacturer_id',
        'model',
        'context',
        'gateway_id',
        'floor',
        'room_id',
        'room_area_id',
        'enable_data_storage',
    ];

    /**
     * <layer number> (2.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string, string|array>
     */
    protected $casts = [
        'context' => AsArrayObject::class,
    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> manufacturer
     * <Function> The manufacturer of the LoRaWAN device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id', 'id');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> gateway
     * <Function> The LoRaWAN gateway associated with the LoRaWAN device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gateway(): BelongsTo
    {
        return $this->belongsTo(LorawanGateway::class, 'gateway_id', 'id');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> room
     * <Function> The room associated with the LoRaWAN device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> roomArea
     * <Function> The room area associated with the LoRaWAN device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function roomArea(): BelongsTo
    {
        return $this->belongsTo(RoomArea::class, 'room_area_id', 'id');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> data
     * <Function> The logged data of the LoRaWAN device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data(): HasMany
    {
        return $this->hasMany(LorawanDeviceData::class, 'device_id', 'id');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> classification
     * <Function> Get device classification according to model and other factor
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function classification(): Attribute
    {
        return Attribute::make(
            get: function () {
                $context = $this->context;

                return match ($this->model) {
                    // milesight switch
                    'WS501', 'WS502', 'WS503' => "switch",

                    // milesight workplace sensor
                    'VS121' => 'ai_sensor',

                    // milesight iot controller
                    'UC300' => (function () use ($context) {
                        // Check slave model
                        return match (@$context['slave_model']) {
                            'ADW300' => 'electric_meter',
                            'RCF230CD' => 'thermostat',
                            'EPM5500P' => 'electric_meter',
                            default => 'unknown_controller'
                        };
                    })(),

                    // acrel electric meter
                    'ADW300' => 'electric_meter',

                    // netvox door and motion sensor
                    'R313A', 'WS202' => 'sensor',

                    // netvox occupancy temp light
                    'RB11E' => 'occu_temp_light',

                    // netvox co2temphumid sensor
                    'RA0715' => 'co2_temp_humid',

                    default => 'unknown'
                };
            }
        );
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> electricMeterData
     * <Function> The data log of the LoRaWAN electric meter device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function electricMeterData(): HasMany
    {
        return $this->hasMany(ElectricMeterData::class, 'device_id', 'id');
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> loadTopNData
     * <Function> Attach top N data per type to each device in $devices.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|array devices
     * @param  int|null $limit
     * @return void
     */
    public static function loadTopNData($devices, ?int $limit = 10): void
    {
        if (empty($devices))
            return;

        if (is_array($devices))
            $devices = collect($devices);

        if (
            $devices instanceof EloquentCollection === false &&
            $devices instanceof Collection === false
        ) {
            $devices = collect($devices);
        }

        if ($devices->isEmpty()) return;

        $ids = $devices->pluck('id');

        $rows = LorawanDeviceData::query()
            ->fromSub(function ($q) use ($ids) {
                $q->select('*')
                    ->selectRaw('ROW_NUMBER() OVER (PARTITION BY device_id, type ORDER BY id DESC) AS rn')
                    ->from('lorawan_device_data')
                    ->whereIn('device_id', $ids);
            }, 'x')
            ->where('rn', '<=', $limit)
            ->orderBy('device_id')->orderBy('type')->orderByDesc('id')
            ->get()
            ->groupBy('device_id');

        foreach ($devices as $d) {
            $d->setRelation('top_data', $rows->get($d->id, collect()));
        }
    }
}
