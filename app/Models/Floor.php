<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Floor extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $fillable     (1.0) The attributes that are mass assignable.
    // $appends      (2.0) The attributes to append on the collection.
    // $timestamps   (3.0) Disable default timestamp as this will not always
    //                     change.
    // rooms         (4.0) Define the relationship with the Room model.
    // slug          (5.0) Generates a slug from the name attribute.

    use HasFactory;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name'];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $appends
     * <Function> The attributes to append on the collection.
     *
     * @var array
     */
    protected $appends = ['slug'];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $timestamps
     * <Function> Disable default timestamp as this will not always change
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> rooms
     * <Function> Define the one-to-many relationship with the Room model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

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
}
