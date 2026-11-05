<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmenityPhoto extends Model
{
    use HasFactory, HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $fillable   (1.0) Mass assignable attributes.
    // amenity     (2.0) The amenity this photo belongs to.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $fillable
     * <Function> Mass assignable attributes for the amenity photo.
     *
     * @var string[]
     */
    protected $fillable = ['amenity_id', 'file_path'];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> amenity
     * <Function> Gets the amenity this photo belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }
}
