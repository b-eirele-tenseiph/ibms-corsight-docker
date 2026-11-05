<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, SoftDeletes;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $fillable                    (1.0) Mass assignable attributes.
    // $hidden                      (2.0) Attributes hidden during serialization.
    // $casts                       (3.0) Attribute type casting.
    // roomAccess                   (4.0) Direct room access assigned to the user.
    // room                         (5.0) Room associated via room access.
    // switchBotLockAccess          (6.0) The SwitchBot Lock and Keypad
    //                                      accesses associated with the user.
    // getHasSwitchBotLockAccess    (7.0) Check if user has switch bot lock
    // Attribute                            access associated with it.
    // promos                       (8.0) Return all of the promos from database
    // receivesBroadcast            (9.0) Receives notifications for User.
    // NotificationsOn

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $fillable
     * <Function> Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'email_verified_at',
        'username',
        'password',
        'password_displayable',
        'password_last_updated_at',
        'hide_promo_popup',
    ];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $hidden
     * <Function> Attributes hidden during serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'password_last_updated_at',
    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $casts
     * <Function> Attribute type casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_last_updated_at' => 'datetime',
            'password_displayable' => 'encrypted',
            'hide_promo_popup' => 'boolean'
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> roomAccess
     * <Function> Direct room access assigned to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function roomAccess()
    {
        return $this->hasOne(RoomAccess::class, 'user_id', 'id');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> room
     * <Function> Room associated to the user via room access.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function room()
    {
        return $this->hasOneThrough(
            Room::class,
            RoomAccess::class,
            'user_id',
            'id',
            'id',
            'room_id'
        );
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> switchBotLockAccess
     * <Function> The SwitchBot Lock and Keypad accesses associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function switchBotLockAccess(): HasOne
    {
        return $this->hasOne(SwitchBotLockAccess::class, 'user_id', 'id');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getHasSwitchBotLockAccessAttribute
     * <Function> Check if user has switch bot lock access associated with it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getHasSwitchBotLockAccessAttribute(): Attribute
    {
        return Attribute::get(fn() => $this->switchBotLockAccess()->exists());
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> promos
     * <Function> Return all of the promos from database.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function promos()
    {
        return Promo::all();
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> receivesBroadcastNotificationsOn
     * <Function> Receives notifications for User.
     *
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        // Broadcast private channel name for this user
        return 'user.' . $this->getKey();
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> orders
     * <Function> The food orders that belongs to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
