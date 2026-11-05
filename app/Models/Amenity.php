<?php

namespace App\Models;

use App\Observers\Amenity\AmenityObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([AmenityObserver::class])]
class Amenity extends Model
{
    use HasFactory, HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded               (1.0) The attributes that are not mass assignable.
    // $casts                 (2.0) The attributes that should be cast.
    // $fillable              (3.0) Mass assignable attributes.
    // amenityCategory        (4.0) Category associated to be amenity.
    // photos                 (5.0) Gets the photos associated with the amenity.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $guarded
     * <Function> The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'attributes' => 'array',
        'is_visible' => 'boolean',
    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $fillable
     * <Function> Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'category_id',
        'photos',
        'attributes',
        'is_visible',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> amenityCategory
     * <Function> Category associated to be amenity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function amenityCategory()
    {
        return $this->belongsTo(AmenityCategory::class, 'category_id', 'id');
    }
    /**
     * <Layer number> (5.0)
     *
     * <Processing name> photos
     * <Function> Gets the photos associated with the amenity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function photos()
    {
        return $this->hasMany(AmenityPhoto::class);
    }
}
