<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPasswordReset extends Model
{
    use HasFactory;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $primaryKey             (1.0) The primary key associated with the table.
    // $incrementing           (2.0) Indicates if the IDs are auto-incrementing.
    // $guarded                (3.0) The attributes that are not mass assignable.
    // $casts                  (4.0) The attributes that should be cast.
    // admin                   (5.0) Relationship for the admin that owns the
    //                               reset token.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $primaryKey
     * <Function> The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'admin_id';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $incrementing
     * <Function> Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $keyTpe = 'string';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $guarded
     * <Function> The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reset_token_expires_at' => 'datetime',
    ];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> admin
     * <Function> Relationship for the admin that owns the reset token.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
