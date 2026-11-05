<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\{Model, Builder};
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ThirdPartyAccessToken extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded         (1.0) A list of attributes that are not mass assignable.
    // $timestamps      (2.0) Indicates if the model should be  timestamped.
    // $hidden          (3.0) The attributes that should be hidden for
    //                        serialization.
    // $casts           (4.0) The attributes that should be cast.
    // isSoonToExpire   (5.0) Accessor to determine if the token is soon to
    //                        expire.
    // scopeOfName      (6.0) Scope a query to only include token of a given
    //                        name.

    use HasUuids;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $guarded
     * <Function> A list of attributes that are not mass assignable.
     *
     * @var array<int,string>
     */
    protected $guarded = [];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $timestamps
     * <Function> Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $hidden
     * <Function> The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = ['token'];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = ['token' => 'encrypted'];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> isSoonToExpire
     * <Function> Accessor to determine if the token is soon to expire.
     *
     * @return bool
     */
    public function isSoonToExpire(): Attribute
    {
        return Attribute::make(
            get: fn() => !blank($this->expires_at) && now()->addMinute()->gte($this->expires_at),
        );
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> scopeOfName
     * <Function> Scope a query to only include token of a given name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    #[Scope]
    protected function ofName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }
}
