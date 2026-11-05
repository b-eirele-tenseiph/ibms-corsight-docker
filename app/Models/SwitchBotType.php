<?php

namespace App\Models;

use App\Models\SwitchBotDevice;
use App\Models\SwitchBotHub;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SwitchBotType extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // CATEGORY_HUB      (1.0) Hub category constant for SwitchBot hub devices.
    // CATEGORY_DEVICE   (2.0) Device category constant for SwitchBot devices.
    // CATEGORY_REMOTE   (3.0) Remote category constant for SwitchBot remote
    //                         control.
    // CATEGORIES        (4.0) The available categories.
    // $fillable         (5.0) The attributes that are mass assignable.
    // devices           (6.0) The devices that belongs to this type.
    // hubs              (7.0) The hubs that belongs to this type.

    use HasFactory, HasUuids;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> CATEGORY_HUB
     * <Function> Hub category constant for SwitchBot hub devices.
     *
     * @var string
     */
    public const CATEGORY_HUB = 'hub';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> CATEGORY_DEVICE
     * <Function> Device category constant for SwitchBot devices.
     *
     * @var string
     */
    public const CATEGORY_DEVICE = 'device';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> CATEGORY_REMOTE
     * <Function> Remote category constant for SwitchBot remote control devices.
     *
     * @var string
     */
    public const CATEGORY_REMOTE = 'remote';

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> CATEGORIES
     * <Function> The available categories.
     *
     * @var array<int,string>
     */
    public const CATEGORIES = [
        self::CATEGORY_HUB,
        self::CATEGORY_DEVICE,
        self::CATEGORY_REMOTE,
    ];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $fillable
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'id',
        'name',
        'description',
        'category'
    ];

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> devices
     * The devices that belongs to this type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function devices(): HasMany
    {
        return $this->hasMany(SwitchBotDevice::class, 'type_id');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> hubs
     * The hubs that belongs to this type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hubs(): HasMany
    {
        return $this->hasMany(SwitchBotHub::class, 'type_id');
    }
}
