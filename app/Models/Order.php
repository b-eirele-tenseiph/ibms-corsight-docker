<?php

namespace App\Models;

use App\Observers\Order\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    use HasFactory;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded                 (1.0) The attributes that are not mass
    //                                assignable.
    // $appends                 (2.0) The attributes to append on the collection.
    // scopeNotCompleted        (3.0) Query scope for all not yet completed
    //                                orders.
    // room                     (4.0) The room that the order belongs to.
    // user                     (5.0) The user that the order belongs to.
    // details                  (6.0) The order details of the order.
    // getOrderAmountAttribute  (7.0) Custom accessor to get total amount of the
    //                                order.

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
     * <Processing name> $appends
     * <Function> The attributes to append on the collection.
     *
     * @var array
     */
    protected $appends = ['order_amount'];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> scopeNotCompleted
     * <Function> Query scope for all not yet completed orders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotCompleted(Builder $query): Builder
    {
        return $query->where('status', '<>', 'completed');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> room
     * <Function> The room that the order belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> user
     * <Function> The user that the order belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> details
     * <Function> The order details of the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getOrderAmountAttribute
     * <Function> Custom accessor to get total amount of the order.
     *
     * @return void
     */
    public function getOrderAmountAttribute()
    {
        return $this->details->sum('total_amount');
    }
}
