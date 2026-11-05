<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Food extends Model
{
    use HasFactory, HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded      (1.0)  The attributes that are not mass assignable.
    // $casts        (2.0)  The attributes that should be cast.
    // category      (3.0)  Food Category relation.

    protected $table = 'foods';
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
        'price' => 'decimal:2', // Converts 'price' column to decimal with 2 decimal places
        'available' => 'boolean', // Converts 'is_available' column to boolean
    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $category
     * <Function> Food Category relation.
     *
     * @var array
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FoodCategory::class, 'category_id');
    }
}
