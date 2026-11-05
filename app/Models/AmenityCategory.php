<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AmenityCategory extends Model
{
    use HasFactory, HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded               (1.0) The attributes that are not mass assignable.
    // $casts                 (2.0) The attributes that should be cast.
    // $fillable              (3.0) Mass assignable attributes.
    // $appends               (4.0) The attributes to append on the collection.
    // slug                   (5.0) Generates a slug from the name attribute.
    // amenities              (6.0) Relation belongs to Amenity.

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
        'name',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $appends
     * <Function> The attributes to append on the collection.
     *
     * @var array
     */
    protected $appends = ['slug'];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> slug
     * <Function> Generates a slug from the name attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn() => Str::slug($this->name),
            set: fn($value) => Str::slug($value ?: $this->name)
        );
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> amenities
     * <Function> Relation belongs to Amenity
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function amenities(): HasMany
    {
        return $this->hasMany(Amenity::class, 'category_id');
    }
}
