<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwitchBotRemoteData extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // VIA_API          (1.0) 'via' attribute when the data was received via
    //                        API request.
    // $fillable        (2.0) The attributes that are mass assignable.
    // $casts           (3.0) The attributes that should be cast.
    // $touches         (4.0) The relationships that should be touched on save.
    // device           (5.0) The device associated with the data.

    use Prunable, HasFactory;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> VIA_API
     * <Function> 'via' attribute when the data was received via API request.
     *
     * @var string
     */
    const VIA_API = 'api';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['via', 'context', 'received_at'];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'received_at' => 'datetime',
        'context' => 'array',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $touches
     * <Function> The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = ['remote'];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> device
     * <Function> The device associated with the data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function remote(): BelongsTo
    {
        return $this->belongsTo(SwitchBotRemote::class, 'device_id', 'id');
    }
}
