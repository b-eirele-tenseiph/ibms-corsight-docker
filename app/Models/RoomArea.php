<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class RoomArea extends Model
{
    use HasFactory, HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $fillable       (1.0) The attributes that are mass assignable.
    // $appends        (2.0) The attributes to append on the collection.
    // $casts          (3.0) The attributes that should be cast to native types.
    // room            (4.0) Get the room associated with the room area.
    // switchBotHubs   (5.0) Get the SwitchBot hubs that belong to this room
    //                       area.
    // slug            (6.0) Generates a slug from the name attribute.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id', 'room_id', 'name', 'has_active_device'];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $appends
     * <Function> The attributes to append on the collection.
     *
     * @var array
     */
    protected $appends = ['slug'];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_active_device' => 'boolean',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> room
     * <Function> Get the room associated with the room area.
     *
     * @return BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> switchBotHubs
     * <Function> Get the SwitchBot hubs that belong to this room area.
     *
     * @return HasMany
     */
    public function switchBotHubs(): HasMany
    {
        return $this->hasMany(SwitchBotHub::class, 'room_area_id', 'id');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> slug
     * <Function> Generates a slug from the name attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn() => Str::slug($this->name),
            set: fn($value) => Str::slug($value ?: $this->name)
        );
    }
}
