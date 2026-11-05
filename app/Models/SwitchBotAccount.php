<?php

namespace App\Models;

use App\Services\SwitchBot\SwitchBotWebhookService;
use App\Traits\Scopes\RoomFilterable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, Builder};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwitchBotAccount extends Model
{
    use HasFactory, HasUuids, RoomFilterable;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // WEBHOOK_SETUP            (1.0)  Webhook action for setting up webhook.
    // WEBHOOK_UPDATE           (2.0)  Webhook action for updating webhook.
    // $guarded                 (3.0)  A list of attributes that are not mass
    //                                 assignable.
    // $hidden                  (4.0)  The attributes that should be hidden for
    //                                 serialization.
    // $casts                   (5.0)  The attributes that should be cast.
    // room                     (6.0)  The room associated to the SwitchBot
    //                                 account.
    // getLastSyncedAtAttribute (7.0)  Show simple last synced at timestamp.
    // webhook                  (8.0)  Custom mutator for setting up or updating
    //                                 webhook.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> WEBHOOK_SETUP
     * <Function> Webhook action for setting up webhook.
     *
     * @var string
     */
    const WEBHOOK_SETUP  = 'setup';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> WEBHOOK_SETUP
     * <Function> Webhook action for updating webhook.
     *
     * @var string
     */
    const WEBHOOK_UPDATE = 'update';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $guarded
     * <Function> A list of attributes that are not mass assignable.
     *
     * @var array<int,string>
     */
    protected $guarded = [];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $hidden
     * <Function> The attributes that should be hidden for serialization.
     *
     * @var array<int,string>
     */
    protected $hidden = ['password', 'access_token', 'secret_key'];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'access_token' => 'encrypted',
        'secret_key'   => 'encrypted',
        'password'     => 'encrypted',
        'webhook'      => 'string',
    ];

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> room
     * <Function> The room associated to the SwitchBot account.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getLastSyncedAtAttribute
     * <Function> Show simple last synced at timestamp.
     *
     * @return string
     */
    public function LastSyncedAt(): Attribute
    {
        $last_synced = @$this->attributes['last_synced_at'];

        return Attribute::make(
            get: fn() => $last_synced ? Carbon::parse($last_synced)->diffForHumans() : ''
        );
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> webhook
     * <Function> Custom mutator for setting up or updating webhook.
     *
     * @param  array $value
     * @return void
     */
    public function webhook(): Attribute
    {
        $service = SwitchBotWebhookService::withAccessTokenAndSecretKey($this->access_token, $this->secret_key);

        return Attribute::make(
            get: fn() => $service->queryUrl(),
            set: fn($value) => $service->{$value['webhook_action']}("{$value['webhook_url']}/api/switchbot/endpoint")
        );
    }
}
