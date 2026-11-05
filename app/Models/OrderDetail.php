<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    use HasFactory, HasUuids, Prunable;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded         (1.0) The attributes that are not mass assignable.
    // food             (2.0) The food that the order detail belongs to.

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
     * <Processing name> food
     * <Function> The food that the order detail belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }
}
