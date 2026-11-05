<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodCategory extends Model
{
    use HasFactory;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded           (1.0)  The attributes that are not mass assignable.
    // CATEGORIES         (2.0)  The available categories.

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
     * <Processing name> CATEGORIES
     * <Function> The available categories.
     *
     * @var array<string>
     */
    const CATEGORIES = [
        'Appetizers',
        'Main course',
        'Desserts',
        'Beverages'
    ];
}
