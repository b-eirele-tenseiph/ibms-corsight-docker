<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;

class Admin extends Authenticable
{
    /** @use HasFactory<\Database\Factories\AdminFactory> */
    use HasFactory, Notifiable,  HasRoles, HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $fillable                         (1.0) The attributes that are mass
    //                                         assignable.
    // $hidden                           (2.0) The attributes that should be
    //                                         hidden for serialization.
    // $casts                            (3.0) The attributes that should be
    //                                         cast.
    // $appends                          (4.0) The attributes that should be
    //                                         appended to the model's array
    //                                         form.
    // getRoleNameAttribute              (5.0) Get the role name of the admin.
    // getRoleAttribute                  (6.0) Custom accessor for getting only
    //                                         one role.
    // scopeNotSuperAdmin                (7.0) Scope the query to only include
    //                                         all non super admins.
    // receivesBroadcastNotificationsOn  (8.0) Override the channel for
    //                                         receiving notification broadcasts
    // scopeStaff                        (9.0) Scope out admin with staff role.
    // passwordResets                   (10.0) Relationship for admin password
    //                                         reset records.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'enabled',
        'password_last_updated_at'
    ];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $hidden
     * <Function> The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $appends
     * <Function> The attributes that should be appended to the
     *            model's array form.
     *
     * @var array
     */

    protected $appends = ['role_name'];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getRoleNameAttribute
     * <Function> Get the role name of the admin.
     *
     * @return string|null
     */
    public function getRoleNameAttribute()
    {
        return $this->roles->pluck('name')->first();  // Assuming only one role per admin
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> getRoleAttribute
     * <Function> Custom accessor for getting only one role.
     *
     * @return \Spatie\Permission\Models\Role|null
     */
    public function getRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> scopeNotSuperAdmin
     * <Function> Scope the query to only include all non super admins.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotSuperAdmin(Builder $query): Builder
    {
        return $query
            ->whereDoesntHave('roles')
            ->orWhereHas('roles', function ($query) {
                $query->where('name', '!=', 'super admin');
            });
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> receivesBroadcastNotificationsOn
     * <Function> Override the channel for receiving notification broadcasts.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'admin.notification.' . $this->id;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> scopeStaff
     * <Function> Scope out admin with staff role.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStaff(Builder $query): Builder
    {
        return $query->whereHas('roles', function ($query) {
            $query->where('name', 'staff');
        });
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> passwordResets
     * <Function> Relationship for admin password reset records.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function passwordResets()
    {
        return $this->hasOne(AdminPasswordReset::class, 'admin_id', 'id');
    }
}
