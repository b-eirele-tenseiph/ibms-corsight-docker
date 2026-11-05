<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorsightAccount extends Model
{
    use HasFactory, HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $table              (1.0)  The table associated with the model.
    // $guarded            (2.0)  A list of attributes that are not mass
    //                            assignable.
    // $hidden             (3.0)  The attributes that should be hidden for
    //                            serialization.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $table
     * <Function> The table associated with the model.
     *
     * @var string
     */
    protected $table = 'corsight_account';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $guarded
     * <Function> A list of attributes that are not mass assignable.
     *
     * @var array<int,string>
     */
    protected $guarded = [];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $hidden
     * <Function> The attributes that should be hidden for serialization.
     *
     * @var array<int,string>
     */
    protected $hidden = ['password', 'endpoint_secret'];
}
