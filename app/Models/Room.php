<?php

namespace App\Models;

use App\Traits\Scopes\FloorFilterable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Room extends Model
{
    use HasFactory, HasUuids, FloorFilterable;
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $fillable                (1.0)  The attributes that are mass assignable.
    // $appends                 (2.0)  The attributes to append on the 
    //                                 collection.
    // $casts                   (3.0)  The attributes that should be cast to
    //                                 native types.
    // roomAreas                (4.0)  HasMany relation: room → room areas.
    // floor                    (5.0)  BelongsTo relation: room → floor.
    // users                    (6.0)  HasMany relation: room → users.
    // switchBotAccount         (7.0)  The SwitchBot account associated to the 
    //                                 room.
    // slug                     (8.0)  Generates a slug from the name attribute.
    // roomAccesses             (9.0)  The Room Accesses associated with the 
    //                                 Room.
    // getStatus                (10.0) Status accessor for getting status based 
    //                                 on access dates.
    // switchBotHubs            (11.0) The SwitchBot hubs associated to the 
    //                                 room.
    // switchBotDevices         (12.0) The SwitchBot devices associated to the 
    //                                 room.
    // switchBotRemotes         (13.0) The SwitchBot remotes associated to the 
    //                                 room through the SwitchBot hub.
    // lorawanDevices           (14.0) The LoRaWAN devices associated to the 
    //                                 room.
    // dailyOccupancy           (15.0) The daily occupancy records associated to 
    //                                 the room.
    // scenes                   (16.0) Retrieve all scenes related to this room,
    //                                 including associated scene devices.
    // switchBotKeypadTouch     (17.0) Get the switchBot keypad touch
    //                                 associated with the room.
    // hasSwitchBotKeypadTouch  (18.0) Check if room has switch bot keypad
    //                                 touch associated with it by checking
    //                                 relationship existence.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = ['number', 'name', 'floor_id', 'created_at', 'external_apps', 'device_status'];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $appends
     * <Function> The attributes to append on the collection.
     *
     * @var array
     */
    protected $appends = ['slug', 'has_switch_bot_keypad_touch'];

    /**
     * <Layer number> (3.0)
     *
     * Processing: $casts
     * Function: The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'device_status' => 'array',
        'external_apps' => 'array',
        'floor_name' => 'array',
    ];

    /**
     * <Layer number> (4.0)
     *
     * Processing: roomAreas
     * Function: Get the room areas associated with the room.
     *
     * @return HasMany
     */
    public function roomAreas(): HasMany
    {
        return $this->hasMany(RoomArea::class);
    }

    /**
     * <Layer number> (5.0)
     *
     * Processing: floor
     * Function: Get the floor that this room belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * <Layer number> (6.0)
     *
     * Processing: users
     * Function: Get the users associated with the room.
     *
     * @return HasMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_accesses', 'room_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * <Layer number> (7.0)
     *
     *
     * <Processing name> switchBotAccount
     * <Function> The SwitchBot account associated to the room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function switchBotAccount(): HasOne
    {
        return $this->hasOne(SwitchBotAccount::class, 'room_id', 'id');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> slug
     * <Function> Generates a slug from the name attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn() => slug($this->name),
            set: fn($value) => slug($value ?: $this->name)
        );
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> roomAccesses
     * <Function> The Room Accesses associated with the Room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roomAccesses(): HasMany
    {
        return $this->hasMany(RoomAccess::class)->orderBy('start_date');
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> getStatus
     * <Function> Status accessor for getting status based on access dates.
     *
     * @param  string                         $date
     * @param  \Illuminate\Support\Collection $roomAccesses
     * @return string
     */
    public function getStatus(string $date, Collection $roomAccesses): string
    {
        // Return early if no room accesses
        if ($roomAccesses->isEmpty()) {
            return 'available';
        }
        $carbonDate = Carbon::parse($date);
        $dateString = $carbonDate->toDateString();
        // Process all room accesses in a single loop
        $hasCheckin = false;
        $hasCheckout = false;
        $isOccupied = false;
        foreach ($roomAccesses as $roomAccess) {
            $startDate = $roomAccess->start_date ? Carbon::parse($roomAccess->start_date) : null;
            $endDate = $roomAccess->end_date ? Carbon::parse($roomAccess->end_date) : null;
            // Check for check-in (start date matches selected date)
            if ($startDate && $startDate->toDateString() === $dateString) {
                $hasCheckin = true;
            }
            // Check for check-out (end date matches selected date)
            if ($endDate && $endDate->toDateString() === $dateString) {
                $hasCheckout = true;
            }
            // Check if room is occupied (selected date is between start and end dates)
            if ($startDate && $endDate && $startDate->lt($carbonDate) && $endDate->gt($carbonDate)) {
                $isOccupied = true;
            }
        }
        // Determine status based on priority (checkout > occupied > checkin > available)
        if ($hasCheckout) {
            return 'check out';
        }
        if ($isOccupied) {
            return 'occupied';
        }
        if ($hasCheckin) {
            return 'check in';
        }
        return 'available';
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> switchBotHubs
     * <Function> The SwitchBot hubs associated to the room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function switchBotHubs(): HasMany
    {
        return $this->hasMany(SwitchBotHub::class, 'room_id', 'id');
    }

    /** <Layer number> (12.0)
     *
     * <Processing name> switchBotDevices
     * <Function> The SwitchBot devices associated to the room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function switchBotDevices(): HasMany
    {
        return $this->hasMany(SwitchBotDevice::class, 'room_id', 'id');
    }


    /**
     * <Layer number> (13.0)
     *
     * <Processing name> switchBotRemotes
     * <Function> The SwitchBot remotes associated to the room through the
     *            SwitchBot hub.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function switchBotRemotes(): HasManyThrough
    {
        return $this->hasManyThrough(
            SwitchBotRemote::class,
            SwitchBotHub::class,
            'room_id',
            'hub_id',
            'id',
            'id'
        );
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> lorawanDevices
     * <Function> The LoRaWAN devices associated to the room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lorawanDevices(): HasMany
    {
        return $this->hasMany(LorawanDevice::class, 'room_id', 'id');
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> dailyOccupancy
     * <Function> The daily occupancy records associated to the room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dailyOccupancy(): HasMany
    {
        return $this->hasMany(DailyOccupancy::class);
    }


    /**
     * <Layer number> (16.0)
     *
     * <Processing name> scenes
     * <Function> Retrieve all scenes related to this room, including associated scene devices.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scenes()
    {
        return $this->hasMany(Scene::class, 'room_id', 'id')->with('sceneDevices');
    }


    /** 
     * <Layer number> (17.0)
     *
     * <Processing name> switchBotKeypadTouch
     * <Function> Get the switchBot keypad touch associated with the room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function switchBotKeypadTouch(): HasMany
    {
        return $this->hasMany(SwitchBotDevice::class, 'room_id', 'id')
            ->ofType('Keypad Touch');
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> hasSwitchBotKeypadTouch
     * <Function> Check if room has switch bot keypad touch associated with it
     *            by checking relationship existence.
     *
     * @return Attribute
     */
    public function hasSwitchBotKeypadTouch(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->switchBotKeypadTouch()->exists(),
        );
    }
}
